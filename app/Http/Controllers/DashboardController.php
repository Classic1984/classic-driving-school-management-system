<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'students' => Student::count(),
            'payments' => Payment::where('status', 'paid')->sum('amount'),
            'instructors' => Instructor::count(),
            'certificates' => Certificate::count(),
        ];

        $outstandingPayments = Enrollment::where('status', '!=', 'completed')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->balance() > 0)
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->due_date?->timestamp ?? PHP_INT_MAX)
            ->take(10)
            ->values();

        return view('dashboard', compact('stats', 'outstandingPayments'));
    }
}
