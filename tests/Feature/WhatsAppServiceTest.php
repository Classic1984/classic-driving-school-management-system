<?php

namespace Tests\Feature;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    protected function fakeTwilioConfig(): void
    {
        config([
            'services.twilio.account_sid' => 'AC-fake-sid',
            'services.twilio.auth_token' => 'fake-token',
            'services.twilio.whatsapp_from' => '+15550001111',
        ]);
    }

    public function test_it_does_nothing_when_twilio_is_not_configured(): void
    {
        config(['services.twilio.account_sid' => null]);
        Http::fake();

        $result = (new WhatsAppService)->send('08031234567', 'HX123', ['1' => 'value']);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_it_does_nothing_when_no_template_sid_is_given(): void
    {
        $this->fakeTwilioConfig();
        Http::fake();

        $result = (new WhatsAppService)->send('08031234567', null, ['1' => 'value']);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_it_normalizes_a_local_nigerian_number_and_sends_a_template_message(): void
    {
        $this->fakeTwilioConfig();
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)]);

        $result = (new WhatsAppService)->send('08031234567', 'HX123abc', ['1' => '95,000.00']);

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'https://api.twilio.com/2010-04-01/Accounts/AC-fake-sid/Messages.json')
                && $request['From'] === 'whatsapp:+15550001111'
                && $request['To'] === 'whatsapp:+2348031234567'
                && $request['ContentSid'] === 'HX123abc'
                && $request['ContentVariables'] === json_encode(['1' => '95,000.00']);
        });
    }

    public function test_it_normalizes_a_number_already_in_international_format(): void
    {
        $this->fakeTwilioConfig();
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SM123'], 201)]);

        (new WhatsAppService)->send('+234 803 123 4567', 'HX123', []);

        Http::assertSent(fn ($request) => $request['To'] === 'whatsapp:+2348031234567');
    }

    public function test_it_returns_false_when_the_phone_number_is_blank(): void
    {
        $this->fakeTwilioConfig();
        Http::fake();

        $result = (new WhatsAppService)->send(null, 'HX123', []);

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_it_returns_false_when_twilio_responds_with_a_failure(): void
    {
        $this->fakeTwilioConfig();
        Http::fake(['api.twilio.com/*' => Http::response(['message' => 'invalid template'], 400)]);

        $result = (new WhatsAppService)->send('08031234567', 'HX123', []);

        $this->assertFalse($result);
    }
}
