<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrolledTraineeController extends Controller
{
    /**
     * The entry point for the Student Login Training workflow: every
     * enrolled student with their training-session count and who last
     * logged a session for them, so staff can find a student and log
     * today's training without going through the full student profile.
     */
    public function index(Request $request): View
    {
        $query = Student::whereHas('courses')
            ->with(['courses', 'attendances' => fn ($inner) => $inner->where('status', 'present')->with('loggedBy')->latest('date')]);

        if ($search = $request->query('search')) {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $trainees = $query->latest('enrollment_date')->paginate(15)->appends($request->query());

        return view('enrolled-trainees.index', compact('trainees'));
    }
}
