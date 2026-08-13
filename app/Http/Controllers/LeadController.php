<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\ActivityLog;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Lead::query();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $leads = $query->latest()->paginate(15)->appends($request->query());

        return view('leads.index', compact('leads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('leads.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $lead = Lead::create($request->validated());

        ActivityLog::record("Logged a new inquiry from {$lead->name}");

        return Redirect::route('leads.index')->with('status', 'lead-created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lead $lead): View
    {
        return view('leads.edit', compact('lead'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        $lead->update($request->validated());

        ActivityLog::record("Updated inquiry for {$lead->name}");

        return Redirect::route('leads.index')->with('status', 'lead-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lead $lead): RedirectResponse
    {
        $name = $lead->name;
        $lead->delete();

        ActivityLog::record("Deleted inquiry for {$name}");

        return Redirect::route('leads.index')->with('status', 'lead-deleted');
    }
}
