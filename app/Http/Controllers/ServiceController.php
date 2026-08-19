<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\Student;
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

    /**
     * Remove the specified resource from storage.
     *
     * A service that has ever been charged to a student is left alone -
     * student_services (and the payment_allocations built on top of it)
     * cascade-delete with it, which would silently erase billing history
     * for anyone who was charged for it. Deactivating it instead keeps
     * that history intact while hiding it from new charges.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $chargedStudents = Student::whereHas('studentServices', fn ($query) => $query->where('service_id', $service->id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Student $student) => ['id' => $student->id, 'name' => $student->name]);

        if ($chargedStudents->isNotEmpty()) {
            return Redirect::route('services.index')->with([
                'status' => 'service-in-use',
                'serviceInUseStudents' => $chargedStudents->all(),
            ]);
        }

        $name = $service->name;
        $service->delete();

        ActivityLog::record("Deleted a billable service ({$name})");

        return Redirect::route('services.index')->with('status', 'service-deleted');
    }
}
