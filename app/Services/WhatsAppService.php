<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message via Twilio using a pre-approved message
     * template. A template (not freeform text) is required because these
     * are business-initiated reminders, not replies inside a customer
     * service window - WhatsApp's own messaging rules block freeform text
     * outside that window. Returns false (without throwing) if Twilio
     * isn't fully configured yet, the given template isn't set, or the
     * number can't be normalized, so an incomplete WhatsApp setup never
     * breaks callers that also try SMS.
     */
    public function send(?string $to, ?string $templateSid, array $variables = []): bool
    {
        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.whatsapp_from');

        if (! $accountSid || ! $authToken || ! $from || ! $templateSid) {
            return false;
        }

        $phone = $this->normalize($to);

        if (! $phone) {
            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => "whatsapp:{$from}",
                'To' => "whatsapp:+{$phone}",
                'ContentSid' => $templateSid,
                'ContentVariables' => json_encode((object) $variables),
            ]);

        if ($response->failed()) {
            Log::warning('Twilio WhatsApp message failed to send.', ['to' => $phone, 'response' => $response->body()]);
        }

        return $response->successful();
    }

    /**
     * Normalize a Nigerian phone number to E.164 digits (234XXXXXXXXXX,
     * no leading "+" or "0") - the "+" is added when building the "To"
     * value since Twilio expects "whatsapp:+234...".
     */
    protected function normalize(?string $phone): ?string
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
