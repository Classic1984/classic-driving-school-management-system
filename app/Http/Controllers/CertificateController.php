<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCertificateRequest;
use App\Http\Requests\UpdateCertificateRequest;
use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Student;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $certificate = Certificate::create($request->validated());
        $certificate->load('student');

        ActivityLog::record("Issued certificate {$certificate->certificate_number} for {$certificate->student->name}");

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
        $certificate->load('student');

        ActivityLog::record("Updated certificate {$certificate->certificate_number} for {$certificate->student->name}");

        return Redirect::route('certificates.index')->with('status', 'certificate-updated');
    }

    /**
     * Send the certificate as a PDF to the student's WhatsApp number - the
     * PDF is generated fresh and stored on the public disk at an
     * unguessable path so Twilio can fetch it by URL, the same pattern
     * already used for corporate quotations/invoices/receipts.
     */
    public function whatsapp(WhatsAppService $whatsapp, Certificate $certificate): RedirectResponse
    {
        $certificate->load(['student', 'course', 'instructor']);

        if (! $whatsapp->isConfigured()) {
            return Redirect::route('certificates.show', $certificate)->with('status', 'certificate-whatsapp-not-configured');
        }

        if (! $certificate->student->phone) {
            return Redirect::route('certificates.show', $certificate)->with('status', 'certificate-whatsapp-no-phone');
        }

        $path = "certificates/{$certificate->certificate_number}-".Str::random(40).'.pdf';
        Storage::disk('public')->put($path, $this->buildPdf($certificate)->output());
        $url = Storage::disk('public')->url($path);

        $sent = $whatsapp->sendDocument(
            $certificate->student->phone,
            $url,
            "Congratulations {$certificate->student->name}! Here's your certificate ({$certificate->certificate_number}) for {$certificate->course->name} from Classic Driving School."
        );

        if (! $sent) {
            return Redirect::route('certificates.show', $certificate)->with('status', 'certificate-whatsapp-failed');
        }

        ActivityLog::record("Sent certificate {$certificate->certificate_number} to {$certificate->student->name} via WhatsApp");

        return Redirect::route('certificates.show', $certificate)->with('status', 'certificate-whatsapp-sent');
    }

    private function buildPdf(Certificate $certificate): PdfDocument
    {
        return Pdf::loadView('certificates.pdf', compact('certificate'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Certificate $certificate): RedirectResponse
    {
        $certificate->load('student');
        $description = "Revoked certificate {$certificate->certificate_number} for {$certificate->student->name}";

        $certificate->delete();

        ActivityLog::record($description);

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
