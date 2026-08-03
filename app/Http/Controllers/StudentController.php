<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Student::with('courses');

        if ($search = $request->query('search')) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($courseId = $request->query('course_id')) {
            $query->whereHas('courses', fn ($inner) => $inner->where('courses.id', $courseId));
        }

        if ($payment = $request->query('payment')) {
            if ($payment === 'locked') {
                $query->whereHas('courses', fn ($inner) => $inner->where('course_student.status', 'locked'));
            } elseif ($payment === 'clear') {
                $query->whereDoesntHave('courses', fn ($inner) => $inner->where('course_student.status', 'locked'));
            }
        }

        $students = $query->latest()->paginate(10)->appends($request->query());
        $courses = Course::orderBy('name')->get();

        return view('students.index', compact('students', 'courses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('student-photos', 'public');
        }

        Student::create($data);

        return Redirect::route('students.index')->with('status', 'student-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): View
    {
        $student->load(['courses', 'payments']);

        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $data = $request->validated();
        unset($data['photo']);

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }

            $data['photo_path'] = $request->file('photo')->store('student-photos', 'public');
        }

        $student->update($data);

        return Redirect::route('students.index')->with('status', 'student-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return Redirect::route('students.index')->with('status', 'student-deleted');
    }
}
