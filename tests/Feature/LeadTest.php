<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_lead_routes(): void
    {
        $lead = Lead::factory()->create();

        $this->get('/leads')->assertRedirect('/login');
        $this->get('/leads/create')->assertRedirect('/login');
        $this->get("/leads/{$lead->id}/edit")->assertRedirect('/login');
        $this->post('/leads', [])->assertRedirect('/login');
        $this->put("/leads/{$lead->id}", [])->assertRedirect('/login');
        $this->delete("/leads/{$lead->id}")->assertRedirect('/login');
    }

    public function test_any_authenticated_staff_can_view_the_lead_index(): void
    {
        $secretary = User::factory()->secretary()->create();
        Lead::factory()->create(['name' => 'Jane Prospect']);

        $response = $this->actingAs($secretary)->get('/leads');

        $response->assertOk();
        $response->assertSee('Jane Prospect');
    }

    public function test_any_authenticated_staff_can_log_an_inquiry(): void
    {
        $secretary = User::factory()->secretary()->create();

        $response = $this->actingAs($secretary)->post('/leads', [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'course_interested' => 'Standard Driving Course',
            'source' => 'Walk-in',
            'status' => 'new',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/leads');

        $this->assertDatabaseHas('leads', [
            'name' => 'John Doe',
            'phone' => '08012345678',
            'status' => 'new',
        ]);
    }

    public function test_storing_a_lead_requires_name_phone_and_a_valid_status(): void
    {
        $secretary = User::factory()->secretary()->create();

        $response = $this->actingAs($secretary)->post('/leads', [
            'name' => '',
            'phone' => '',
            'status' => 'not-a-real-status',
        ]);

        $response->assertSessionHasErrors(['name', 'phone', 'status']);
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_any_authenticated_staff_can_update_a_lead(): void
    {
        $secretary = User::factory()->secretary()->create();
        $lead = Lead::factory()->create(['status' => 'new']);

        $response = $this->actingAs($secretary)->put("/leads/{$lead->id}", [
            'name' => $lead->name,
            'phone' => $lead->phone,
            'status' => 'contacted',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/leads');

        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_any_authenticated_staff_can_delete_a_lead(): void
    {
        $secretary = User::factory()->secretary()->create();
        $lead = Lead::factory()->create();

        $response = $this->actingAs($secretary)->delete("/leads/{$lead->id}");

        $response->assertRedirect('/leads');
        $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
    }

    public function test_the_lead_index_can_be_filtered_by_status(): void
    {
        $secretary = User::factory()->secretary()->create();
        Lead::factory()->create(['name' => 'New Lead', 'status' => 'new']);
        Lead::factory()->create(['name' => 'Converted Lead', 'status' => 'converted']);

        $response = $this->actingAs($secretary)->get('/leads?status=converted');

        $response->assertOk();
        $response->assertSee('Converted Lead');
        $response->assertDontSee('New Lead');
    }

    public function test_the_edit_page_links_to_student_registration_prefilled_with_the_leads_details(): void
    {
        $secretary = User::factory()->secretary()->create();
        $lead = Lead::factory()->create(['name' => 'Prefill Test', 'phone' => '08099999999']);

        $response = $this->actingAs($secretary)->get("/leads/{$lead->id}/edit");

        $response->assertOk();
        $response->assertSee(e(route('students.create', ['name' => 'Prefill Test', 'phone' => '08099999999'])), false);
    }

    public function test_the_student_registration_form_is_prefilled_from_lead_query_params(): void
    {
        $secretary = User::factory()->secretary()->create();

        $response = $this->actingAs($secretary)->get('/students/create?name=Prefill+Test&phone=08099999999');

        $response->assertOk();
        $response->assertSee('value="Prefill Test"', false);
        $response->assertSee('value="08099999999"', false);
    }
}
