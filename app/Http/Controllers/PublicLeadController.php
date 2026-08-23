<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicLeadRequest;
use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

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
            'email' => 'Email',
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
            'course_interested' => $data['course'] ?? null,
            'source' => 'Website',
            'notes' => Str::of($notes)->trim()->value() ?: null,
            'status' => 'new',
        ]);

        ActivityLog::record("New website booking inquiry from {$lead->name}");

        return response()->json(['success' => true], 201);
    }
}
