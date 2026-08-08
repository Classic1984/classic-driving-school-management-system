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
     * everything and create/edit students, training logins, and payments,
     * but cannot manage courses/instructors, delete anything, or access
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
}
