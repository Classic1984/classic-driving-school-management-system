<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Collection;

/**
 * Resolves a student's open (balance > 0) charges across every billing
 * surface - training, per-course certificate fees, and flat catalog
 * services - into one normalized list. Used both to render the
 * multi-service payment entry screen and to validate what's submitted
 * from it, so the two can never disagree about what a student actually
 * owes.
 */
class StudentChargeResolver
{
    /**
     * @return Collection<int, array{type: string, id: int, label: string, price: float, paid: float, balance: float}>
     */
    public static function openCharges(Student $student): Collection
    {
        $charges = collect();

        foreach ($student->courses()->get() as $course) {
            $enrollment = $course->pivot;

            if ($enrollment->balance() > 0) {
                $charges->push([
                    'type' => 'training',
                    'id' => $enrollment->id,
                    'label' => "Training — {$course->name}",
                    'price' => $enrollment->fee(),
                    'paid' => $enrollment->amountPaid(),
                    'balance' => $enrollment->balance(),
                ]);
            }

            if ($enrollment->online_certificate_fee !== null && $enrollment->onlineCertificateBalance() > 0) {
                $charges->push([
                    'type' => 'online_certificate',
                    'id' => $enrollment->id,
                    'label' => "Online Certificate — {$course->name}",
                    'price' => (float) $enrollment->online_certificate_fee,
                    'paid' => (float) $enrollment->online_certificate_fee - $enrollment->onlineCertificateBalance(),
                    'balance' => $enrollment->onlineCertificateBalance(),
                ]);
            }

            if ($enrollment->student_certificate_fee !== null && $enrollment->studentCertificateBalance() > 0) {
                $charges->push([
                    'type' => 'student_certificate',
                    'id' => $enrollment->id,
                    'label' => "Student Certificate — {$course->name}",
                    'price' => (float) $enrollment->student_certificate_fee,
                    'paid' => (float) $enrollment->student_certificate_fee - $enrollment->studentCertificateBalance(),
                    'balance' => $enrollment->studentCertificateBalance(),
                ]);
            }
        }

        foreach ($student->studentServices()->with('service')->get() as $studentService) {
            if ($studentService->balance() > 0) {
                $charges->push([
                    'type' => 'service',
                    'id' => $studentService->id,
                    'label' => $studentService->service->name,
                    'price' => (float) $studentService->price,
                    'paid' => $studentService->amountPaid(),
                    'balance' => $studentService->balance(),
                ]);
            }
        }

        return $charges;
    }

    /**
     * Find one specific open charge by type/id, scoped to this student, or
     * null if it doesn't exist or isn't currently open.
     *
     * @return array{type: string, id: int, label: string, price: float, paid: float, balance: float}|null
     */
    public static function find(Student $student, string $type, int $id): ?array
    {
        return self::openCharges($student)
            ->first(fn (array $charge) => $charge['type'] === $type && $charge['id'] === $id);
    }
}
