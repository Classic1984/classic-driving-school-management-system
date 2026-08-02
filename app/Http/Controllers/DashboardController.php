<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
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

        return view('dashboard', compact('stats'));
    }
}
