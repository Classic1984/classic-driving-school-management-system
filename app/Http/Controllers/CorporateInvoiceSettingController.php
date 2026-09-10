<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCorporateInvoiceSettingRequest;
use App\Models\ActivityLog;
use App\Models\CorporateInvoiceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CorporateInvoiceSettingController extends Controller
{
    /**
     * Show the form for editing the single settings row.
     */
    public function edit(): View
    {
        $settings = CorporateInvoiceSetting::current();

        return view('corporate.invoice-settings.edit', compact('settings'));
    }

    /**
     * Update the single settings row.
     */
    public function update(UpdateCorporateInvoiceSettingRequest $request): RedirectResponse
    {
        $settings = CorporateInvoiceSetting::current();
        $data = $request->safe()->except(['signature', 'remove_signature']);

        // Prefixes are optional - the numbering itself is fully automatic
        // either way, but defaulting an emptied field here (rather than
        // leaving it blank) keeps the settings page showing what's
        // actually in effect.
        $data['invoice_prefix'] = ($data['invoice_prefix'] ?? null) ?: 'INV';
        $data['quotation_prefix'] = ($data['quotation_prefix'] ?? null) ?: 'QUO';
        $data['receipt_prefix'] = ($data['receipt_prefix'] ?? null) ?: 'REC';

        if ($request->boolean('remove_signature') && $settings->signature_path) {
            Storage::disk('public')->delete($settings->signature_path);
            $data['signature_path'] = null;
        }

        if ($request->hasFile('signature')) {
            if ($settings->signature_path) {
                Storage::disk('public')->delete($settings->signature_path);
            }

            $data['signature_path'] = $request->file('signature')->store('corporate/signatures', 'public');
        }

        $settings->update([
            ...$data,
            'updated_by' => $request->user()->id,
        ]);

        ActivityLog::record('Updated the Corporate Invoicing settings');

        return Redirect::route('corporate-invoice-settings.edit')->with('status', 'settings-updated');
    }
}
