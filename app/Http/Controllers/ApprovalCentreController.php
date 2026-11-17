<?php

namespace App\Http\Controllers;

use App\Models\AssessmentRequest;
use App\Models\DiscountRequest;
use App\Models\EnrollmentUpgradeRequest;
use App\Models\Expense;
use App\Models\StudentCorrectionRequest;
use Illuminate\View\View;

class ApprovalCentreController extends Controller
{
    /**
     * Director-only unified inbox of every pending approval across the
     * app - discount requests, correction requests, instructor assessment
     * recommendations, enrollment upgrade requests, and expense requests
     * today, with more request types expected to join this same feed
     * over time. Approving or rejecting an item still goes through its
     * own existing route; this page only aggregates what's pending into
     * one place.
     */
    public function index(): View
    {
        $discountRequests = DiscountRequest::with(['student', 'course', 'requestedBy'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (DiscountRequest $request) => [
                'type' => 'discount',
                'model' => $request,
                'created_at' => $request->created_at,
            ]);

        $correctionRequests = StudentCorrectionRequest::with(['student', 'requestedBy'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (StudentCorrectionRequest $request) => [
                'type' => 'correction',
                'model' => $request,
                'created_at' => $request->created_at,
            ]);

        $assessmentRequests = AssessmentRequest::with(['student', 'course', 'requestedBy'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (AssessmentRequest $request) => [
                'type' => 'assessment',
                'model' => $request,
                'created_at' => $request->created_at,
            ]);

        $enrollmentUpgradeRequests = EnrollmentUpgradeRequest::with(['student', 'fromCourse', 'toCourse', 'requestedBy'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (EnrollmentUpgradeRequest $request) => [
                'type' => 'enrollment_upgrade',
                'model' => $request,
                'created_at' => $request->created_at,
            ]);

        $expenseRequests = Expense::with(['requestedBy'])
            ->where('status', 'pending')
            ->get()
            ->map(fn (Expense $expense) => [
                'type' => 'expense',
                'model' => $expense,
                'created_at' => $expense->created_at,
            ]);

        $approvals = $discountRequests->concat($correctionRequests)
            ->concat($assessmentRequests)
            ->concat($enrollmentUpgradeRequests)
            ->concat($expenseRequests)
            ->sortByDesc('created_at')
            ->values();

        return view('approvals.index', compact('approvals'));
    }
}
