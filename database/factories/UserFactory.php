<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'director',
            // Two factor authentication is enforced for Director accounts
            // (see EnsureDirectorHasTwoFactorEnabled), which would block
            // every existing test's factory-made Director from reaching
            // anything but the profile page. Defaulting to "already set
            // up" here (a real secret, confirmed) keeps every test
            // unrelated to two-factor auth itself behaving as it did
            // before that enforcement existed; tests that specifically
            // need an unconfirmed Director use the withoutTwoFactor()
            // state below.
            'two_factor_secret' => 'TESTINGSECRETTESTINGSECRETTESTI',
            'two_factor_confirmed_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user has the restricted "admin" role: can view
     * everything and create/edit students and payments, but cannot manage
     * courses/instructors/training logins, delete anything, or access
     * Finance.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * Indicate that the user has the "secretary" role — the same
     * restricted access as Admin (this is the role that used to be
     * called "staff" before the rename; its access never changed).
     */
    public function secretary(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'secretary',
        ]);
    }

    /**
     * Indicate that the user has the "director" role (elevated access,
     * plus exclusive access to the Finance section).
     */
    public function director(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'director',
        ]);
    }

    /**
     * Indicate that the model has not (yet) confirmed two-factor
     * authentication - the real state a brand new account starts in.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
