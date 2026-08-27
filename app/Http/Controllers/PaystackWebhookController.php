<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\LeadPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaystackWebhookController extends Controller
{
    /**
     * Receive a Paystack webhook for the online booking deposit. This is
     * the only source of truth for "the customer actually paid" - the
     * booking page's own browser-side payment callback is never trusted to
     * create anything on its own, since a closed tab or a lost connection
     * would otherwise mean a paid deposit with no booking behind it.
     *
     * The booking page passes every detail the walk-in booking form
     * collects (name, phone, course, preferred date/time, etc.) as
     * Paystack transaction metadata when it initializes the payment, so
     * it comes back here unchanged and this handler never has to trust
     * anything the browser sends directly.
     */
    public function handle(Request $request): JsonResponse
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Rejected a Paystack webhook with an invalid signature.');

            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payload = $request->json()->all();

        if (($payload['event'] ?? null) !== 'charge.success') {
            // Paystack sends many event types; a deposit booking only
            // cares about a successful charge, so anything else is
            // acknowledged and ignored rather than treated as an error.
            return response()->json(['message' => 'Ignored']);
        }

        $data = $payload['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (blank($reference)) {
            Log::warning('Rejected a Paystack charge.success webhook with no reference.');

            return response()->json(['message' => 'Missing reference'], 400);
        }

        // Paystack retries a webhook until it receives a 200, so the same
        // successful charge can arrive more than once - this is the
        // idempotency guard that stops it from ever becoming two bookings.
        if (LeadPayment::where('reference', $reference)->exists()) {
            return response()->json(['message' => 'Already processed']);
        }

        $metadata = $data['metadata'] ?? [];

        DB::transaction(function () use ($data, $reference, $metadata) {
            $lead = Lead::create([
                'name' => $metadata['name'] ?? 'Online Booking',
                'phone' => $metadata['phone'] ?? '',
                'course_interested' => $metadata['course'] ?? null,
                'source' => 'Website',
                'notes' => $this->buildNotes($metadata),
                'status' => 'new',
            ]);

            LeadPayment::create([
                'lead_id' => $lead->id,
                'reference' => $reference,
                'gateway' => 'paystack',
                'amount' => ($data['amount'] ?? 0) / 100,
                'currency' => $data['currency'] ?? 'NGN',
                'status' => 'success',
                'paid_at' => $data['paid_at'] ?? now(),
                'raw_payload' => $data,
            ]);

            ActivityLog::record("Online booking deposit received from {$lead->name} (ref: {$reference})");
        });

        return response()->json(['message' => 'Processed']);
    }

    /**
     * HMAC-SHA512 of the raw request body, keyed with the Paystack secret
     * key - Paystack's documented signing scheme. Verified against the
     * raw bytes rather than a re-encoded payload, since re-serializing
     * JSON is not guaranteed to reproduce the exact bytes that were
     * signed.
     */
    protected function hasValidSignature(Request $request): bool
    {
        $secret = config('services.paystack.secret_key');

        if (blank($secret)) {
            return false;
        }

        $signature = $request->header('x-paystack-signature');

        if (blank($signature)) {
            return false;
        }

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function buildNotes(array $metadata): ?string
    {
        $notes = collect([
            'email' => 'Email',
            'transmission' => 'Transmission',
            'preferred_date' => 'Preferred date',
            'preferred_time' => 'Preferred time',
            'message' => 'Message',
        ])->map(fn (string $label, string $field) => filled($metadata[$field] ?? null)
            ? "{$label}: {$metadata[$field]}"
            : null)
            ->filter()
            ->implode("\n");

        return Str::of($notes)->trim()->value() ?: null;
    }
}
