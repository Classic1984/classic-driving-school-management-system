<?php

namespace Tests\Feature;

use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorporateCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_access_corporate_companies(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/corporate-companies')->assertForbidden();
    }

    public function test_a_director_can_view_the_companies_list(): void
    {
        $director = User::factory()->director()->create();
        CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);

        $response = $this->actingAs($director)->get('/corporate-companies');

        $response->assertOk();
        $response->assertSee('Arco Worldwide');
    }

    public function test_a_director_can_register_a_new_company(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->post('/corporate-companies', [
            'name' => 'Arco Worldwide',
            'address' => '12 Marina Road',
            'city' => 'Port Harcourt',
            'contact_person' => 'Mr. John',
            'phone' => '08012345678',
            'email' => 'company@email.com',
            'notes' => 'Sends drivers twice a year.',
        ]);

        $company = CorporateCompany::firstWhere('name', 'Arco Worldwide');
        $response->assertRedirect(route('corporate-companies.show', $company));
        $this->assertDatabaseHas('corporate_companies', [
            'name' => 'Arco Worldwide',
            'city' => 'Port Harcourt',
            'created_by' => $director->id,
        ]);
    }

    public function test_a_new_company_is_assigned_a_sequential_reference_number(): void
    {
        $director = User::factory()->director()->create();

        $first = CorporateCompany::factory()->create();
        $second = CorporateCompany::factory()->create();

        $this->assertSame('COMP-'.str_pad((string) $first->id, 5, '0', STR_PAD_LEFT), $first->company_reference);
        $this->assertSame('COMP-'.str_pad((string) $second->id, 5, '0', STR_PAD_LEFT), $second->company_reference);
        $this->assertNotSame($first->company_reference, $second->company_reference);

        // A director viewing the list should see both real reference
        // numbers, not the (deliberately) uneditable id itself.
        $this->actingAs($director)
            ->get('/corporate-companies')
            ->assertSee($first->company_reference)
            ->assertSee($second->company_reference);
    }

    public function test_a_director_can_update_a_company(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($director)->put("/corporate-companies/{$company->id}", [
            'name' => 'New Name',
            'address' => $company->address,
            'city' => $company->city,
            'contact_person' => $company->contact_person,
            'phone' => $company->phone,
            'email' => $company->email,
        ]);

        $response->assertRedirect(route('corporate-companies.show', $company));
        $this->assertDatabaseHas('corporate_companies', ['id' => $company->id, 'name' => 'New Name']);
    }

    public function test_a_director_can_delete_a_company_with_no_quotations_or_invoices(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();

        $response = $this->actingAs($director)->delete("/corporate-companies/{$company->id}");

        $response->assertRedirect(route('corporate-companies.index'));
        $this->assertDatabaseMissing('corporate_companies', ['id' => $company->id]);
    }

    public function test_the_company_page_links_to_creating_and_viewing_its_quotations_and_invoices(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $quotation = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id]);
        $invoice = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}");

        $response->assertOk();
        $response->assertSee(route('corporate-quotations.create', ['corporate_company_id' => $company->id]), false);
        $response->assertSee(route('corporate-invoices.create', ['corporate_company_id' => $company->id]), false);
        $response->assertSee(route('corporate-quotations.show', $quotation), false);
        $response->assertSee(route('corporate-invoices.show', $invoice), false);
    }

    public function test_the_companies_list_can_be_searched_by_name(): void
    {
        $director = User::factory()->director()->create();
        CorporateCompany::factory()->create(['name' => 'Arco Worldwide']);
        CorporateCompany::factory()->create(['name' => 'Bright Logistics']);

        $response = $this->actingAs($director)->get('/corporate-companies?search=Arco');

        $response->assertSee('Arco Worldwide');
        $response->assertDontSee('Bright Logistics');
    }
}
