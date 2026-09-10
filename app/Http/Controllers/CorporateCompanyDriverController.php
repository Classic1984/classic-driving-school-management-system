<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateCompanyDriverRequest;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateCompanyDriver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class CorporateCompanyDriverController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateCompanyDriverRequest $request, CorporateCompany $corporateCompany): RedirectResponse
    {
        $driver = $corporateCompany->drivers()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record("Added driver {$driver->name} to corporate client ({$corporateCompany->name})");

        return Redirect::to(route('corporate-companies.show', $corporateCompany).'#drivers')->with('status', 'driver-added');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CorporateCompanyDriver $corporateCompanyDriver): RedirectResponse
    {
        $company = $corporateCompanyDriver->company;
        $name = $corporateCompanyDriver->name;

        $corporateCompanyDriver->delete();

        ActivityLog::record("Removed driver {$name} from corporate client ({$company->name})");

        return Redirect::route('corporate-companies.show', $company)->with('status', 'driver-removed');
    }
}
