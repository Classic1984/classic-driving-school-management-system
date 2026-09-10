<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateCompanyRequest;
use App\Http\Requests\UpdateCorporateCompanyRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CorporateCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $companies = CorporateCompany::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->withCount(['quotations', 'invoices'])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('corporate.companies.index', [
            'companies' => $companies,
            'search' => $search,
            'stats' => $this->computeStats(),
        ]);
    }

    /**
     * Dashboard stat tiles shown above the companies list. There's no
     * separate "Corporate Dashboard" page - directors get one entry point
     * into this whole area (Companies), so the overview lives right here.
     *
     * @return array<string, int|float>
     */
    private function computeStats(): array
    {
        $invoices = CorporateInvoice::with(['items', 'payments'])->get();

        $active = $invoices->reject(fn (CorporateInvoice $invoice) => $invoice->status === 'cancelled');
        $overdue = $active->filter(fn (CorporateInvoice $invoice) => $invoice->isOverdue());
        $paid = $invoices->where('status', 'paid');
        $pending = $active->reject(fn (CorporateInvoice $invoice) => $invoice->status === 'paid' || $invoice->isOverdue());

        return [
            'totalInvoices' => $invoices->count(),
            'pendingInvoices' => $pending->count(),
            'paidInvoices' => $paid->count(),
            'overdueInvoices' => $overdue->count(),
            'totalOutstanding' => $active->sum(fn (CorporateInvoice $invoice) => $invoice->balance()),
            'totalCollected' => $invoices->sum(fn (CorporateInvoice $invoice) => $invoice->amountPaid()),
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('corporate.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateCompanyRequest $request): RedirectResponse
    {
        $company = CorporateCompany::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record("Added a corporate client ({$company->name})");

        return Redirect::route('corporate-companies.show', $company)->with('status', 'company-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, CorporateCompany $corporateCompany): View
    {
        $corporateCompany->load(['quotations' => fn ($query) => $query->latest(), 'invoices' => fn ($query) => $query->latest()]);

        $quotationStatus = $request->query('quotation_status');
        $invoiceStatus = $request->query('invoice_status');

        return view('corporate.companies.show', [
            'company' => $corporateCompany,
            'quotationStatus' => $quotationStatus,
            'invoiceStatus' => $invoiceStatus,
            // Filtered in PHP rather than SQL because "expired"/"overdue"
            // are computed display states (see displayStatus()), not
            // values the status column itself ever holds.
            'filteredQuotations' => $quotationStatus
                ? $corporateCompany->quotations->filter(fn ($quotation) => $quotation->displayStatus() === $quotationStatus)->values()
                : $corporateCompany->quotations,
            'filteredInvoices' => $invoiceStatus
                ? $corporateCompany->invoices->filter(fn ($invoice) => $invoice->displayStatus() === $invoiceStatus)->values()
                : $corporateCompany->invoices,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CorporateCompany $corporateCompany): View
    {
        return view('corporate.companies.edit', ['company' => $corporateCompany]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCorporateCompanyRequest $request, CorporateCompany $corporateCompany): RedirectResponse
    {
        $corporateCompany->update($request->validated());

        ActivityLog::record("Updated a corporate client ({$corporateCompany->name})");

        return Redirect::route('corporate-companies.show', $corporateCompany)->with('status', 'company-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CorporateCompany $corporateCompany): RedirectResponse
    {
        if ($corporateCompany->quotations()->exists() || $corporateCompany->invoices()->exists()) {
            return Redirect::route('corporate-companies.index')->with('status', 'company-in-use');
        }

        $name = $corporateCompany->name;
        $corporateCompany->delete();

        ActivityLog::record("Deleted a corporate client ({$name})");

        return Redirect::route('corporate-companies.index')->with('status', 'company-deleted');
    }
}
