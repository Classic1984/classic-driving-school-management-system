<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingProgressController extends Controller
{
    /**
     * The full, paginated version of the Dashboard's "Student Training
     * Progress" table, which is itself capped to the 15 most recently
     * enrolled students only.
     */
    public function index(Request $request): View
    {
        $enrollments = Enrollment::with(['student', 'course'])
            ->latest('enrolled_at')
            ->paginate(20)
            ->appends($request->query());

        return view('training-progress.index', compact('enrollments'));
    }
}
