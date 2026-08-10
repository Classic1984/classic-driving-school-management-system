<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class CertificateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $certificates = Certificate::with(['student', 'course', 'instructor'])->latest('issue_date')->paginate(10);

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('certificates.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCertificateRequest $request): RedirectResponse
    {
        Certificate::create($request->validated());

        return Redirect::route('certificates.index')->with('status', 'certificate-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Certificate $certificate): View
    {
        $certificate->load(['student', 'course', 'instructor']);

        return view('certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Certificate $certificate): View
    {
        return view('certificates.edit', [...$this->formOptions(), 'certificate' => $certificate]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCertificateRequest $request, Certificate $certificate): RedirectResponse
    {
        $certificate->update($request->validated());

        return Redirect::route('certificates.index')->with('status', 'certificate-updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate): RedirectResponse
    {
        $certificate->delete();

        return Redirect::route('certificates.index')->with('status', 'certificate-deleted');
    }

    /**
     * Get the option lists shared by the create and edit forms.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'students' => Student::orderBy('name')->get(),
            'courses' => Course::orderBy('name')->get(),
            'instructors' => Instructor::orderBy('name')->get(),
        ];
    }
}
