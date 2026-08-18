<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Service;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Charges a newly-registered student for the "Additional Offers" ticked
 * alongside their course (Driver's License, Learner's Permit, and any
 * other active catalog Service), and - if any money was entered toward
 * Training or the offers at registration time - records it as a single
 * payment allocated across exactly those charges. Mirrors
 * PaymentAllocationController::store()'s charge-then-allocate pattern,
 * adapted for a student/enrollment that was only just created in the
 * same request.
 */
class AdditionalOfferService
{
    /**
     * @param  array<int, int>  $serviceIds  Catalog service IDs ticked as Additional Offers.
     * @param  array<int, float>  $serviceAmounts  service_id => amount to pay toward it now.
     */
    public function chargeAndAllocate(
        Student $student,
        Enrollment $enrollment,
        array $serviceIds,
        array $serviceAmounts,
        ?float $trainingAmount,
        ?string $paymentMethod,
        User $actor,
        Carbon $paymentDate,
    ): void {
        DB::transaction(function () use ($student, $enrollment, $serviceIds, $serviceAmounts, $trainingAmount, $paymentMethod, $actor, $paymentDate) {
            $studentServices = collect($serviceIds)
                ->mapWithKeys(function (int $serviceId) use ($student) {
                    $service = Service::findOrFail($serviceId);
                    $studentService = $student->studentServices()->create([
                        'service_id' => $service->id,
                        'price' => $service->price,
                    ]);

                    ActivityLog::record("Charged {$student->name} ₦".number_format((float) $service->price, 2)." for {$service->name}");

                    return [$serviceId => $studentService];
                });

            $totalAmount = (float) $trainingAmount + array_sum($serviceAmounts);

            if ($totalAmount <= 0) {
                return;
            }

            $payment = Payment::create([
                'student_id' => $student->id,
                'course_id' => null,
                'amount' => $totalAmount,
                'payment_date' => $paymentDate->toDateString(),
                'payment_method' => $paymentMethod,
                'status' => 'paid',
                'recorded_by' => $actor->id,
            ]);

            $paidFor = [];

            if ($trainingAmount > 0) {
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'training',
                    'enrollment_id' => $enrollment->id,
                    'amount' => $trainingAmount,
                ]);

                $paidFor[] = "Training — {$enrollment->course->name}";
            }

            foreach ($serviceAmounts as $serviceId => $amount) {
                if ($amount <= 0 || ! $studentServices->has($serviceId)) {
                    continue;
                }

                $studentService = $studentServices->get($serviceId);

                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'allocation_type' => 'service',
                    'student_service_id' => $studentService->id,
                    'amount' => $amount,
                ]);

                $studentService->maybeAutoStartProcessing();
                $paidFor[] = $studentService->service->name;
            }

            ActivityLog::record('Recorded a payment of ₦'.number_format($totalAmount, 2)." for {$student->name} (".implode(', ', $paidFor).')');
        });

        $enrollment->refreshStatus();
    }
}
