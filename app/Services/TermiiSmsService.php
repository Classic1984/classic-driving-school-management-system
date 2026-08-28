<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiSmsService
{
    /**
     * Send an SMS via Termii to a Nigerian phone number. Returns false
     * (without throwing) if no API key is configured or the number can't
     * be normalized, so a missing SMS setup never breaks the callers that
     * also send an email reminder alongside it.
     */
    public function send(?string $to, string $message): bool
    {
        $apiKey = config('services.termii.api_key');

        if (! $apiKey) {
            return false;
        }

        $phone = $this->normalize($to);

        if (! $phone) {
            return false;
        }

        $response = Http::post('https://api.ng.termii.com/api/sms/send', [
            'to' => $phone,
            'from' => config('services.termii.sender_id'),
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic',
            'api_key' => $apiKey,
        ]);

        if ($response->failed()) {
            Log::warning('Termii SMS failed to send.', ['to' => $phone, 'response' => $response->body()]);
        }

        return $response->successful();
    }

    /**
     * Normalize a Nigerian phone number to Termii's expected international
     * format (234XXXXXXXXXX, no leading "+" or "0"). Public so callers
     * that need to match a user-entered number against a stored one
     * (e.g. instructor phone+PIN login) can normalize both sides the
     * same way, regardless of which format either was typed in.
     */
    public function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '234'.substr($digits, 1);
        }

        if (str_starts_with($digits, '234')) {
            return $digits;
        }

        return '234'.$digits;
    }
}
