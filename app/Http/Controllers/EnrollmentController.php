<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class EnrollmentController extends Controller
{
    /**
     * Mark an enrollment as completed, automatically issuing the student's
     * certificate for it. Requires the outstanding balance to be cleared
     * first; once completed, the enrollment is exempt from future locking.
     */
    public function complete(Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->status === 'completed') {
            return Redirect::back()->with('status', 'enrollment-already-completed');
        }

        if ($enrollment->balance() > 0) {
            return Redirect::back()->withErrors([
                'enrollment' => 'Cannot mark this course complete: outstanding balance of '.number_format($enrollment->balance(), 2).' must be cleared first.',
            ]);
        }

        $enrollment->markCompleted();

        return Redirect::back()->with('status', 'enrollment-completed');
    }
}
