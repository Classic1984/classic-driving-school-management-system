<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorDashboardController extends Controller
{
    /**
     * Placeholder landing page for an instructor's first successful
     * login - today's schedule, attendance marking, and assessment
     * recording are their own follow-up phases, not this one.
     */
    public function index(Request $request): View
    {
        return view('instructor.dashboard', ['instructor' => $request->user()->instructor]);
    }
}
