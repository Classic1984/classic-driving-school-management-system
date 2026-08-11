<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Resolves a student's charges across every billing surface - training,
 * per-course certificate fees, and flat catalog services - into one
 * normalized list. Used to render the student's financial overview and
 * the multi-service payment entry screen, and to validate what's
 * submitted from the latter, so none of the three can ever disagree
 * about what a student owes.
 */
class StudentChargeResolver
{
    /**
     * Every charge a student has, regardless of payment status.
     *
     * @return Collection<int, array{type: string, id: int, label: string, price: float, paid: float, balance: float, status: string}>
     */
    public static function allCharges(Student $student): Collection
    {
        $charges = collect();

        foreach ($student->courses()->get() as $course) {
            $enrollment = $course->pivot;

            $charges->push(self::charge('training', $enrollment->id, "Training — {$course->name}", $enrollment->fee(), $enrollment->balance()));

            if ($enrollment->online_certificate_fee !== null) {
                $charges->push(self::charge('online_certificate', $enrollment->id, "Online Certificate — {$course->name}", (float) $enrollment->online_certificate_fee, $enrollment->onlineCertificateBalance()));
            }

            if ($enrollment->student_certificate_fee !== null) {
                $charges->push(self::charge('student_certificate', $enrollment->id, "Student Certificate — {$course->name}", (float) $enrollment->student_certificate_fee, $enrollment->studentCertificateBalance()));
            }
        }

        foreach ($student->studentServices()->with('service')->get() as $studentService) {
            $charges->push(self::charge('service', $studentService->id, $studentService->service->name, (float) $studentService->price, $studentService->balance()));
        }

        return $charges;
    }

    /**
     * Only the charges with a balance still owed, plus every active
     * catalog service the student hasn't been charged for yet - what the
     * payment entry screen actually needs to offer, so staff can bill and
     * pay for a new service (e.g. Driver's License Processing) in the
     * same action instead of charging it first on the student's page and
     * coming back here afterward.
     *
     * @return Collection<int, array{type: string, id: int, label: string, price: float, paid: float, balance: float, status: string}>
     */
    public static function openCharges(Student $student): Collection
    {
        $charges = self::allCharges($student)->filter(fn (array $charge) => $charge['balance'] > 0)->values();

        return $charges->concat(self::availableServices($student));
    }

    /**
     * Find one specific open charge by type/id, scoped to this student, or
     * null if it doesn't exist or isn't currently open.
     *
     * @return array{type: string, id: int, label: string, price: float, paid: float, balance: float, status: string}|null
     */
    public static function find(Student $student, string $type, int $id): ?array
    {
        return self::openCharges($student)
            ->first(fn (array $charge) => $charge['type'] === $type && $charge['id'] === $id);
    }

    /**
     * The active catalog services this student hasn't been charged for
     * yet, presented as candidate charges (type "new_service") the
     * payment entry screen can bill and pay for in one step.
     *
     * @return Collection<int, array{type: string, id: int, label: string, price: float, paid: float, balance: float, status: string}>
     */
    protected static function availableServices(Student $student): Collection
    {
        $chargedServiceIds = $student->studentServices()->pluck('service_id');

        return Service::where('is_active', true)
            ->whereNotIn('id', $chargedServiceIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service) => self::charge('new_service', $service->id, $service->name, (float) $service->price, (float) $service->price));
    }

    /**
     * @return array{type: string, id: int, label: string, price: float, paid: float, balance: float, status: string}
     */
    protected static function charge(string $type, int $id, string $label, float $price, float $balance): array
    {
        $paid = $price - $balance;

        return [
            'type' => $type,
            'id' => $id,
            'label' => $label,
            'price' => $price,
            'paid' => $paid,
            'balance' => $balance,
            'status' => match (true) {
                $paid <= 0 => 'unpaid',
                $balance > 0 => 'part_payment',
                default => 'paid',
            },
        ];
    }
}
