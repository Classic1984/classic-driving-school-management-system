<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DiscountAuditLog;
use App\Models\DiscountRequest;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DiscountRequestController extends Controller
{
    /**
     * Director-only inbox of every pending discount request across all
     * students.
     */
    public function index(): View
    {
        $discountRequests = DiscountRequest::with(['student', 'course', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('discount-requests.index', compact('discountRequests'));
    }

    /**
     * Approve a discount request: apply it to the enrollment it was raised
     * against (reducing the fee) and log it the same way a Director
     * applying a discount directly is logged.
     */
    public function approve(DiscountRequest $discountRequest): RedirectResponse
    {
        $discountRequest->load(['student', 'course']);
        $enrollment = Enrollment::findOrFail($discountRequest->enrollment_id);

        $enrollment->forceFill([
            'discount_percentage' => $discountRequest->discount_percentage,
            'discount_amount' => $discountRequest->discount_amount,
            'discount_reason' => $discountRequest->reason,
            'discount_reason_note' => $discountRequest->reason_note,
            'discount_approved_by' => request()->user()->id,
            'fee' => $discountRequest->final_fee,
        ])->save();

        DiscountAuditLog::create([
            'student_id' => $discountRequest->student_id,
            'course_id' => $discountRequest->course_id,
            'applied_by' => request()->user()->id,
            'original_fee' => $discountRequest->original_fee,
            'discount_percentage' => $discountRequest->discount_percentage,
            'discount_amount' => $discountRequest->discount_amount,
            'final_fee' => $discountRequest->final_fee,
            'reason' => $discountRequest->reason,
            'reason_note' => $discountRequest->reason_note,
        ]);

        $discountRequest->update([
            'status' => 'approved',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        $enrollment->refreshStatus();

        ActivityLog::record('Approved a ₦'.number_format((float) $discountRequest->discount_amount, 2)." discount for {$discountRequest->student->name}'s enrollment in {$discountRequest->course->name}");

        return Redirect::route('discount-requests.index')->with('status', 'discount-request-approved');
    }

    /**
     * Reject a discount request. The enrollment stays at the full fee it
     * was created with - nothing changes on it.
     */
    public function reject(DiscountRequest $discountRequest): RedirectResponse
    {
        $discountRequest->load(['student', 'course']);

        $discountRequest->update([
            'status' => 'rejected',
            'resolved_by' => request()->user()->id,
            'resolved_at' => now(),
        ]);

        ActivityLog::record("Rejected a discount request for {$discountRequest->student->name}'s enrollment in {$discountRequest->course->name}");

        return Redirect::route('discount-requests.index')->with('status', 'discount-request-rejected');
    }
}
