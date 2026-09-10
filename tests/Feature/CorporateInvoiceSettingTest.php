<?php

namespace Tests\Feature;

use App\Models\CorporateInvoiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorporateInvoiceSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_secretary_cannot_access_invoice_settings(): void
    {
        $secretary = User::factory()->secretary()->create();

        $this->actingAs($secretary)->get('/corporate-invoice-settings')->assertForbidden();
    }

    public function test_a_director_sees_default_prefixes_the_first_time(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->get('/corporate-invoice-settings');

        $response->assertOk();
        // Checking the actual input value attributes, not just that "QUO"
        // appears somewhere on the page (the hint text below each field
        // already contains it regardless of the input's real value) -
        // this is what regressed when CorporateInvoiceSetting::current()
        // didn't hydrate the freshly-created row's column defaults.
        $response->assertSee('value="QUO"', false);
        $response->assertSee('value="INV"', false);
        $response->assertSee('value="REC"', false);
    }

    public function test_a_director_can_update_the_company_and_bank_details(): void
    {
        $director = User::factory()->director()->create();

        $response = $this->actingAs($director)->put('/corporate-invoice-settings', [
            'company_name' => 'Classic Driving School & Son Nigeria Limited',
            'bank_account_name' => 'Classic Driving School & Son Nigeria Limited',
            'bank_name' => 'Access Bank',
            'bank_account_number' => '1436990473',
            'phone' => '08068878663',
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
        ]);

        $response->assertRedirect(route('corporate-invoice-settings.edit'));
        $this->assertDatabaseHas('corporate_invoice_settings', [
            'bank_name' => 'Access Bank',
            'bank_account_number' => '1436990473',
            'updated_by' => $director->id,
        ]);
    }

    public function test_a_director_can_upload_and_remove_a_signature(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();

        $upload = $this->actingAs($director)->put('/corporate-invoice-settings', [
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
            'signature' => UploadedFile::fake()->image('signature.png'),
        ]);
        $upload->assertRedirect();

        $settings = CorporateInvoiceSetting::current();
        $this->assertNotNull($settings->signature_path);
        Storage::disk('public')->assertExists($settings->signature_path);
        $originalPath = $settings->signature_path;

        $remove = $this->actingAs($director)->put('/corporate-invoice-settings', [
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
            'remove_signature' => '1',
        ]);
        $remove->assertRedirect();

        $this->assertNull(CorporateInvoiceSetting::current()->refresh()->signature_path);
        Storage::disk('public')->assertMissing($originalPath);
    }

    public function test_settings_only_ever_have_one_row(): void
    {
        CorporateInvoiceSetting::current();
        CorporateInvoiceSetting::current();
        CorporateInvoiceSetting::current();

        $this->assertDatabaseCount('corporate_invoice_settings', 1);
    }

    public function test_signature_data_uri_is_null_without_an_uploaded_signature(): void
    {
        $this->assertNull(CorporateInvoiceSetting::current()->signatureDataUri());
    }

    public function test_next_sequence_increments_within_a_year_and_resets_the_next(): void
    {
        $this->assertSame(1, CorporateInvoiceSetting::nextSequence('invoice', 2026));
        $this->assertSame(2, CorporateInvoiceSetting::nextSequence('invoice', 2026));
        $this->assertSame(3, CorporateInvoiceSetting::nextSequence('invoice', 2026));
        $this->assertSame(1, CorporateInvoiceSetting::nextSequence('invoice', 2027));
        $this->assertSame(2, CorporateInvoiceSetting::nextSequence('invoice', 2027));
    }

    public function test_next_sequence_is_tracked_independently_per_document_type(): void
    {
        $this->assertSame(1, CorporateInvoiceSetting::nextSequence('invoice', 2026));
        $this->assertSame(2, CorporateInvoiceSetting::nextSequence('invoice', 2026));
        $this->assertSame(1, CorporateInvoiceSetting::nextSequence('quotation', 2026));
        $this->assertSame(1, CorporateInvoiceSetting::nextSequence('receipt', 2026));
        $this->assertSame(3, CorporateInvoiceSetting::nextSequence('invoice', 2026));
    }

    public function test_signature_data_uri_embeds_the_uploaded_image(): void
    {
        Storage::fake('public');
        $director = User::factory()->director()->create();

        $this->actingAs($director)->put('/corporate-invoice-settings', [
            'invoice_prefix' => 'INV',
            'quotation_prefix' => 'QUO',
            'receipt_prefix' => 'REC',
            'signature' => UploadedFile::fake()->image('signature.png'),
        ]);

        $dataUri = CorporateInvoiceSetting::current()->signatureDataUri();

        $this->assertNotNull($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }
}
