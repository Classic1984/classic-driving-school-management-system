<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    /**
     * Placeholder landing page for a student's first successful login -
     * their own training progress, payments, and certificate status are
     * their own follow-up phases, not this one. Mirrors
     * InstructorDashboardController's original sub-phase-1 placeholder.
     */
    public function index(Request $request): View
    {
        return view('student.dashboard', ['student' => $request->user()->student]);
    }
}
