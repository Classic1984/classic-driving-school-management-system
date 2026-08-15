<?php

namespace Database\Factories;

use App\Models\MessageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageLog>
 */
class MessageLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipient_type' => 'student',
            'recipient_id' => fake()->numberBetween(1, 1000),
            'recipient_name' => fake()->name(),
            'recipient_phone' => '0803'.fake()->numerify('#######'),
            'purpose' => fake()->randomElement(array_keys(MessageLog::PURPOSES)),
            'channel' => 'sms',
            'status' => 'sent',
            'message' => fake()->sentence(),
        ];
    }
}
