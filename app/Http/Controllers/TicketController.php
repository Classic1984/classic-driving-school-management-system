<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $tickets = Ticket::with(['student', 'course', 'instructor'])->latest('date')->paginate(10);

        return view('tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('tickets.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $student = Student::findOrFail($validated['student_id']);

        Ticket::create([
            ...$validated,
            'ticket_number' => $this->generateTicketNumber($student, $validated['course_id'], $validated['date']),
            'payment_status' => 'cleared',
        ]);

        // Issuing a ticket is proof the student attended, so mark them
        // present automatically instead of requiring a separate manual step.
        Attendance::firstOrCreate(
            [
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
                'date' => $validated['date'],
            ],
            [
                'instructor_id' => $validated['instructor_id'] ?? null,
                'status' => 'present',
            ]
        );

        return Redirect::route('tickets.index')->with('status', 'ticket-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): View
    {
        $ticket->load(['student', 'course', 'instructor']);

        return view('tickets.show', compact('ticket'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ticket $ticket): View
    {
        return view('tickets.edit', [...$this->formOptions(), 'ticket' => $ticket]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($request->validated());

        return Redirect::route('tickets.index')->with('status', 'ticket-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return Redirect::route('tickets.index')->with('status', 'ticket-deleted');
    }

    /**
     * Get the option lists shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'students' => Student::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'instructors' => Instructor::orderBy('name')->get(),
        ];
    }

    /**
     * Build the ticket number from the student's permanent ID number, so
     * every ticket issued to a student is traceable back to them. Unique
     * because StoreTicketRequest already enforces one ticket per
     * student/course/date before this runs.
     */
    protected function generateTicketNumber(Student $student, int $courseId, string $date): string
    {
        $formattedDate = Carbon::parse($date)->format('Ymd');

        return "{$student->student_id_number}-{$courseId}-{$formattedDate}";
    }
}
