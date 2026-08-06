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
            'payments' => Payment::where('status', 'paid')->whereDate('payment_date', today())->sum('amount'),
            'instructors' => Instructor::count(),
            'certificates' => Certificate::count(),
        ];

        $paymentTotals = [
            'week' => Payment::where('status', 'paid')
                ->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('amount'),
            'month' => Payment::where('status', 'paid')
                ->whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
            'all_time' => Payment::where('status', 'paid')->sum('amount'),
        ];

        $outstandingPayments = Enrollment::where('status', '!=', 'completed')
            ->with(['student', 'course'])
            ->get()
            ->filter(fn (Enrollment $enrollment) => $enrollment->balance() > 0)
            ->sortBy(fn (Enrollment $enrollment) => $enrollment->due_date?->timestamp ?? PHP_INT_MAX)
            ->take(10)
            ->values();

        $trainingProgress = Enrollment::with(['student', 'course'])
            ->latest('enrolled_at')
            ->take(15)
            ->get();

        return view('dashboard', compact('stats', 'paymentTotals', 'outstandingPayments', 'trainingProgress'));
    }
}
