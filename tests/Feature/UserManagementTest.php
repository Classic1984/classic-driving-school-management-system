<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_staff_routes(): void
    {
        $user = User::factory()->create();

        $this->get('/users')->assertRedirect('/login');
        $this->get('/users/create')->assertRedirect('/login');
        $this->post('/users', [])->assertRedirect('/login');
        $this->get("/users/{$user->id}/edit")->assertRedirect('/login');
        $this->put("/users/{$user->id}", [])->assertRedirect('/login');
        $this->delete("/users/{$user->id}")->assertRedirect('/login');
    }

    public function test_only_a_director_can_manage_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $secretary = User::factory()->secretary()->create();
        $director = User::factory()->director()->create();
        $target = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/users')->assertForbidden();
        $this->actingAs($secretary)->get('/users')->assertForbidden();
        $this->actingAs($director)->get('/users')->assertOk();

        $this->actingAs($admin)->get('/users/create')->assertForbidden();
        $this->actingAs($director)->get('/users/create')->assertOk();

        $this->actingAs($admin)->post('/users', [])->assertForbidden();
        $this->actingAs($admin)->delete("/users/{$target->id}")->assertForbidden();
    }

    public function test_the_summary_counts_split_instructors_from_administrators(): void
    {
        $director = User::factory()->director()->create();
        User::factory()->admin()->create();
        User::factory()->secretary()->create();
        User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($director)->get('/users');

        $response->assertOk();
        // director + admin + secretary + instructor = 4 total, 1 instructor,
        // 3 administrators (everyone else).
        $response->assertSeeInOrder(['4', 'Total Staff']);
        $response->assertSeeInOrder(['1', 'Instructors']);
        $response->assertSeeInOrder(['3', 'Administrators']);
    }

    public function test_the_index_accepts_a_valid_per_page_and_ignores_an_invalid_one(): void
    {
        $director = User::factory()->director()->create();

        $this->actingAs($director)->get('/users?per_page=25')->assertOk();
        $this->actingAs($director)->get('/users?per_page=9999')->assertOk();
    }

    public function test_a_director_can_create_a_staff_account(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/users', [
            'name' => 'New Secretary',
            'email' => 'newsecretary@example.com',
            'role' => 'secretary',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/users');

        $user = User::where('email', 'newsecretary@example.com')->firstOrFail();
        $this->assertSame('secretary', $user->role);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_creating_a_staff_account_requires_a_valid_role(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'superadmin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'newuser@example.com']);
    }

    public function test_a_director_can_update_a_staff_accounts_role(): void
    {
        $director = User::factory()->director()->create();
        $staff = User::factory()->admin()->create(['name' => 'Staff Member']);

        $response = $this->actingAs($director)->put("/users/{$staff->id}", [
            'name' => 'Staff Member',
            'email' => $staff->email,
            'role' => 'secretary',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('secretary', $staff->fresh()->role);
    }

    public function test_a_director_can_remove_a_staff_account(): void
    {
        $director = User::factory()->director()->create();
        $staff = User::factory()->admin()->create();

        $this->actingAs($director)->delete("/users/{$staff->id}")->assertRedirect('/users');

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_a_director_cannot_remove_their_own_account(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->delete("/users/{$director->id}");

        $response->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $director->id]);
    }
}
