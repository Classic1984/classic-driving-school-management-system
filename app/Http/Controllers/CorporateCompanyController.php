<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateCompanyRequest;
use App\Http\Requests\UpdateCorporateCompanyRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
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

        return view('corporate.companies.index', compact('companies', 'search'));
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
    public function show(CorporateCompany $corporateCompany): View
    {
        $corporateCompany->load(['quotations' => fn ($query) => $query->latest(), 'invoices' => fn ($query) => $query->latest()]);

        return view('corporate.companies.show', ['company' => $corporateCompany]);
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
