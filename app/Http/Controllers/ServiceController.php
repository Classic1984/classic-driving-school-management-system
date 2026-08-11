<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\ActivityLog;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $services = Service::orderBy('name')->get();

        return view('services.index', compact('services'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $service = Service::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record("Added a billable service ({$service->name})");

        return Redirect::route('services.index')->with('status', 'service-created');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service): View
    {
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record("Updated a billable service ({$service->name})");

        return Redirect::route('services.index')->with('status', 'service-updated');
    }
}
