<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateInvoiceRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CorporateInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $invoices = CorporateInvoice::with(['company', 'items'])
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        return view('corporate.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companies = CorporateCompany::orderBy('name')->get(['id', 'name']);

        return view('corporate.invoices.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateInvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = CorporateInvoice::create([
                ...$request->safe()->except('items'),
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $index => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return $invoice;
        });

        ActivityLog::record("Created corporate invoice {$invoice->invoice_number} for {$invoice->company->name}");

        return Redirect::route('corporate-invoices.show', $invoice)->with('status', 'invoice-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(CorporateInvoice $corporateInvoice): View
    {
        $corporateInvoice->load(['company', 'items', 'payments']);
        $settings = CorporateInvoiceSetting::current();

        return view('corporate.invoices.show', ['invoice' => $corporateInvoice, 'settings' => $settings]);
    }

    /**
     * Mark the invoice as sent to the company.
     */
    public function send(Request $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        if ($corporateInvoice->status !== 'pending') {
            return Redirect::back()->with('status', 'invoice-cannot-send');
        }

        $corporateInvoice->update([
            'status' => 'sent',
            'sent_by' => $request->user()->id,
            'sent_at' => now(),
        ]);

        ActivityLog::record("Sent corporate invoice {$corporateInvoice->invoice_number} to {$corporateInvoice->company->name}");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-sent');
    }

    /**
     * Cancel the invoice. Blocked once it's fully paid - undoing a paid
     * invoice is a refund/reversal decision, not a cancellation, and isn't
     * handled here.
     */
    public function cancel(Request $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        if ($corporateInvoice->status === 'paid') {
            return Redirect::back()->with('status', 'invoice-cannot-cancel');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        $corporateInvoice->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->string('cancellation_reason'),
        ]);

        ActivityLog::record("Cancelled corporate invoice {$corporateInvoice->invoice_number} ({$corporateInvoice->cancellation_reason})");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-cancelled');
    }
}
