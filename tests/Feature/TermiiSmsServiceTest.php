<?php

namespace Tests\Feature;

use App\Services\TermiiSmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TermiiSmsServiceTest extends TestCase
{
    public function test_it_does_nothing_when_no_api_key_is_configured(): void
    {
        config(['services.termii.api_key' => null]);
        Http::fake();

        $result = (new TermiiSmsService)->send('08031234567', 'Hello');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_it_normalizes_a_local_nigerian_number_and_sends(): void
    {
        config(['services.termii.api_key' => 'fake-key', 'services.termii.sender_id' => 'TestSender']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '123'], 200)]);

        $result = (new TermiiSmsService)->send('08031234567', 'Hello there');

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.ng.termii.com/api/sms/send'
                && $request['to'] === '2348031234567'
                && $request['from'] === 'TestSender'
                && $request['sms'] === 'Hello there'
                && $request['api_key'] === 'fake-key';
        });
    }

    public function test_it_normalizes_a_number_already_in_international_format(): void
    {
        config(['services.termii.api_key' => 'fake-key']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message_id' => '123'], 200)]);

        (new TermiiSmsService)->send('+234 803 123 4567', 'Hello');

        Http::assertSent(fn ($request) => $request['to'] === '2348031234567');
    }

    public function test_it_returns_false_when_the_phone_number_is_blank(): void
    {
        config(['services.termii.api_key' => 'fake-key']);
        Http::fake();

        $result = (new TermiiSmsService)->send(null, 'Hello');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_it_returns_false_when_termii_responds_with_a_failure(): void
    {
        config(['services.termii.api_key' => 'fake-key']);
        Http::fake(['api.ng.termii.com/*' => Http::response(['message' => 'invalid api key'], 401)]);

        $result = (new TermiiSmsService)->send('08031234567', 'Hello');

        $this->assertFalse($result);
    }
}
