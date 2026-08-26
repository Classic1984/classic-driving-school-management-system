<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicLeadRequest;
use App\Mail\BookingConfirmationMail;
use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PublicLeadController extends Controller
{
    /**
     * Capture a booking inquiry submitted from the public marketing site
     * (classicdriving.com.ng) as a Lead, so it lands directly in the
     * pipeline instead of only an email nobody re-enters by hand.
     */
    public function store(StorePublicLeadRequest $request): JsonResponse
    {
        $data = $request->validated();

        // A bot fills the hidden honeypot field; a human never sees it.
        // Report success without persisting anything, so the bot gets no
        // signal that its submission was singled out and dropped.
        if (filled($data['botcheck'] ?? null)) {
            return response()->json(['success' => true]);
        }

        $notes = collect([
            'transmission' => 'Transmission',
            'preferred_date' => 'Preferred date',
            'preferred_time' => 'Preferred time',
            'message' => 'Message',
        ])->map(fn (string $label, string $field) => filled($data[$field] ?? null)
            ? "{$label}: {$data[$field]}"
            : null)
            ->filter()
            ->implode("\n");

        $lead = Lead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'course_interested' => $data['course'] ?? null,
            'source' => 'Website',
            'notes' => Str::of($notes)->trim()->value() ?: null,
            'status' => 'new',
        ]);

        ActivityLog::record("New website booking inquiry from {$lead->name}");

        if (filled($lead->email)) {
            $this->sendBookingConfirmation($lead, $data);
        }

        return response()->json(['success' => true], 201);
    }

    /**
     * Email the customer an immediate booking confirmation. Best-effort,
     * the same way the Lead capture itself is: a mail provider hiccup
     * never turns a successful booking into an error response for the
     * visitor, since the Lead is already safely recorded by this point.
     *
     * @param  array<string, mixed>  $data
     */
    protected function sendBookingConfirmation(Lead $lead, array $data): void
    {
        [$programmeName, $duration] = $this->splitCourseLabel($data['course'] ?? null);

        try {
            Mail::to($lead->email)->send(new BookingConfirmationMail(
                lead: $lead,
                programmeName: $programmeName ?? 'To be confirmed',
                duration: $duration ?? 'N/A',
                startDate: $this->formatStartDate($data['preferred_date'] ?? null),
                trainingType: $data['transmission'] ?? 'N/A',
            ));
        } catch (Throwable $e) {
            Log::warning("Booking confirmation email to {$lead->email} failed: {$e->getMessage()}");
        }
    }

    /**
     * The booking form's "Course" dropdown packs a programme name,
     * duration, and price into one string (e.g. "Non-Experience (Auto &
     * Manual) — 4 Weeks — ₦95,000"), separated by em dashes. A flat
     * catalog service like "Learner's Permit Trainee — ₦6,000" has no
     * duration segment at all, so this returns null for it rather than
     * guessing.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function splitCourseLabel(?string $course): array
    {
        if (blank($course)) {
            return [null, null];
        }

        $parts = array_map('trim', explode('—', $course));

        if (count($parts) && str_starts_with(end($parts), '₦')) {
            array_pop($parts);
        }

        return [$parts[0] ?? null, $parts[1] ?? null];
    }

    /**
     * The date input submits a raw "YYYY-MM-DD" value - render it the way
     * a person would write it in a letter. Falls back to whatever was
     * submitted if it isn't a parseable date, rather than hiding it.
     */
    protected function formatStartDate(?string $date): string
    {
        if (blank($date)) {
            return 'To be confirmed';
        }

        try {
            return Carbon::parse($date)->format('F j, Y');
        } catch (Throwable) {
            return $date;
        }
    }
}
