<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateQuotationRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateQuotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CorporateQuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $quotations = CorporateQuotation::with(['company', 'items'])
            ->latest('issue_date')
            ->paginate(20)
            ->withQueryString();

        return view('corporate.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companies = CorporateCompany::orderBy('name')->get(['id', 'name']);

        return view('corporate.quotations.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateQuotationRequest $request): RedirectResponse
    {
        $quotation = DB::transaction(function () use ($request) {
            $quotation = CorporateQuotation::create([
                ...$request->safe()->except('items'),
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $index => $item) {
                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return $quotation;
        });

        ActivityLog::record("Created corporate quotation {$quotation->quotation_number} for {$quotation->company->name}");

        return Redirect::route('corporate-quotations.show', $quotation)->with('status', 'quotation-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(CorporateQuotation $corporateQuotation): View
    {
        $corporateQuotation->load(['company', 'items', 'convertedInvoice']);

        return view('corporate.quotations.show', ['quotation' => $corporateQuotation]);
    }

    /**
     * Mark the quotation as sent to the company.
     */
    public function send(Request $request, CorporateQuotation $corporateQuotation): RedirectResponse
    {
        if ($corporateQuotation->status === 'converted') {
            return Redirect::back()->with('status', 'quotation-already-converted');
        }

        $corporateQuotation->update([
            'status' => 'sent',
            'sent_by' => $request->user()->id,
            'sent_at' => now(),
        ]);

        ActivityLog::record("Sent corporate quotation {$corporateQuotation->quotation_number} to {$corporateQuotation->company->name}");

        return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-sent');
    }

    /**
     * Convert an approved quotation into an invoice, carrying its company,
     * training details, and line items across so nothing has to be
     * retyped.
     */
    public function convert(Request $request, CorporateQuotation $corporateQuotation): RedirectResponse
    {
        if ($corporateQuotation->status === 'converted') {
            return Redirect::route('corporate-invoices.show', $corporateQuotation->converted_invoice_id)
                ->with('status', 'quotation-already-converted');
        }

        $invoice = DB::transaction(function () use ($request, $corporateQuotation) {
            $invoice = CorporateInvoice::create([
                'corporate_company_id' => $corporateQuotation->corporate_company_id,
                'corporate_quotation_id' => $corporateQuotation->id,
                'invoice_date' => now()->format('Y-m-d'),
                'due_date' => now()->addWeek()->format('Y-m-d'),
                'programme_name' => $corporateQuotation->programme_name,
                'duration_label' => $corporateQuotation->duration_label,
                'participant_count' => $corporateQuotation->participant_count,
                'course_coverage' => $corporateQuotation->course_coverage,
                'created_by' => $request->user()->id,
            ]);

            foreach ($corporateQuotation->items as $item) {
                $invoice->items()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'sort_order' => $item->sort_order,
                ]);
            }

            $corporateQuotation->update([
                'status' => 'converted',
                'converted_invoice_id' => $invoice->id,
            ]);

            return $invoice;
        });

        ActivityLog::record("Converted corporate quotation {$corporateQuotation->quotation_number} to invoice {$invoice->invoice_number}");

        return Redirect::route('corporate-invoices.show', $invoice)->with('status', 'invoice-created');
    }
}
