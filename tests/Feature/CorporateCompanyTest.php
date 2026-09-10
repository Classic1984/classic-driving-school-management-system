<?php

namespace Tests\Feature;

use App\Models\CorporateCompany;
use App\Models\CorporateCompanyDriver;
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

    public function test_the_companies_list_shows_a_delete_button_for_a_company_with_no_documents(): void
    {
        $director = User::factory()->director()->create();
        CorporateCompany::factory()->create();

        $response = $this->actingAs($director)->get('/corporate-companies');

        $response->assertOk();
        $response->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_the_companies_list_hides_the_delete_button_for_a_company_with_documents(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        CorporateInvoice::factory()->create(['corporate_company_id' => $company->id]);

        $response = $this->actingAs($director)->get('/corporate-companies');

        $response->assertOk();
        $response->assertDontSee('name="_method" value="DELETE"', false);
        $response->assertSee('cannot be deleted');
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

    public function test_the_companies_index_shows_invoice_dashboard_stats(): void
    {
        $director = User::factory()->director()->create();

        $paid = CorporateInvoice::factory()->create(['status' => 'paid']);
        $paid->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 50000, 'sort_order' => 0]);
        $paid->payments()->create(['amount' => 50000, 'payment_method' => 'cash', 'payment_date' => now(), 'recorded_by' => $director->id]);

        $pending = CorporateInvoice::factory()->create(['status' => 'pending', 'due_date' => now()->addWeek()->format('Y-m-d')]);
        $pending->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 30000, 'sort_order' => 0]);

        $overdue = CorporateInvoice::factory()->create(['status' => 'sent', 'due_date' => now()->subWeek()->format('Y-m-d')]);
        $overdue->items()->create(['description' => 'Training', 'quantity' => 1, 'unit_price' => 20000, 'sort_order' => 0]);

        CorporateInvoice::factory()->create(['status' => 'cancelled']);

        $response = $this->actingAs($director)->get('/corporate-companies');

        $response->assertOk();
        $response->assertViewHas('stats', [
            'totalInvoices' => 4, // including the cancelled one
            'pendingInvoices' => 1,
            'paidInvoices' => 1,
            'overdueInvoices' => 1,
            'totalOutstanding' => 50000.0, // 30,000 pending + 20,000 overdue; cancelled excluded
            'totalCollected' => 50000.0,
        ]);
    }

    public function test_a_companys_quotations_can_be_filtered_by_status(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $draft = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id, 'status' => 'draft']);
        $sent = CorporateQuotation::factory()->create(['corporate_company_id' => $company->id, 'status' => 'sent']);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}?quotation_status=sent");

        $response->assertOk();
        $response->assertSee($sent->quotation_number);
        $response->assertDontSee($draft->quotation_number);
    }

    public function test_a_companys_invoices_can_be_filtered_by_status(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $pending = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id, 'status' => 'pending']);
        $paid = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id, 'status' => 'paid']);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}?invoice_status=paid");

        $response->assertOk();
        $response->assertSee($paid->invoice_number);
        $response->assertDontSee($pending->invoice_number);
    }

    public function test_a_companys_overdue_invoices_can_be_filtered_even_though_overdue_is_not_a_stored_status(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $overdue = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id, 'status' => 'sent', 'due_date' => now()->subWeek()->format('Y-m-d')]);
        $onTime = CorporateInvoice::factory()->create(['corporate_company_id' => $company->id, 'status' => 'sent', 'due_date' => now()->addWeek()->format('Y-m-d')]);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}?invoice_status=overdue");

        $response->assertOk();
        $response->assertSee($overdue->invoice_number);
        $response->assertDontSee($onTime->invoice_number);
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

    public function test_a_director_can_add_a_driver_to_a_company(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();

        $response = $this->actingAs($director)->post("/corporate-companies/{$company->id}/drivers", [
            'name' => 'Musa Ibrahim',
            'phone' => '08098765432',
            'license_number' => 'DL-12345',
        ]);

        $response->assertRedirect(route('corporate-companies.show', $company).'#drivers');
        $this->assertDatabaseHas('corporate_company_drivers', [
            'corporate_company_id' => $company->id,
            'name' => 'Musa Ibrahim',
            'phone' => '08098765432',
            'license_number' => 'DL-12345',
            'created_by' => $director->id,
        ]);
    }

    public function test_a_secretary_cannot_add_a_driver_to_a_company(): void
    {
        $secretary = User::factory()->secretary()->create();
        $company = CorporateCompany::factory()->create();

        $this->actingAs($secretary)
            ->post("/corporate-companies/{$company->id}/drivers", ['name' => 'Musa Ibrahim'])
            ->assertForbidden();
    }

    public function test_adding_a_driver_requires_a_name(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();

        $this->actingAs($director)
            ->post("/corporate-companies/{$company->id}/drivers", ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_the_company_page_lists_its_enrolled_drivers(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create([
            'corporate_company_id' => $company->id,
            'name' => 'Chidi Okafor',
        ]);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}");

        $response->assertOk();
        $response->assertSee('Chidi Okafor');
        $response->assertSee(route('corporate-company-drivers.destroy', $driver), false);
    }

    public function test_the_company_page_links_a_driver_to_prefilled_student_registration(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create([
            'corporate_company_id' => $company->id,
            'name' => 'Musa Ibrahim',
            'phone' => '08098765432',
            'license_number' => 'DL-12345',
        ]);

        $response = $this->actingAs($director)->get("/corporate-companies/{$company->id}");

        $response->assertOk();
        $response->assertSee(e(route('students.create', [
            'name' => 'Musa Ibrahim',
            'phone' => '08098765432',
            'license_number' => 'DL-12345',
        ])), false);
    }

    public function test_a_director_can_remove_a_driver_from_a_company(): void
    {
        $director = User::factory()->director()->create();
        $company = CorporateCompany::factory()->create();
        $driver = CorporateCompanyDriver::factory()->create(['corporate_company_id' => $company->id]);

        $response = $this->actingAs($director)->delete("/corporate-company-drivers/{$driver->id}");

        $response->assertRedirect(route('corporate-companies.show', $company));
        $this->assertDatabaseMissing('corporate_company_drivers', ['id' => $driver->id]);
    }
}
