<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateQuotationRequest;
use App\Mail\CorporateQuotationMail;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\CorporateQuotation;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    public function create(Request $request): View
    {
        $companies = CorporateCompany::orderBy('name')->get(['id', 'name']);
        $selectedCompanyId = $request->query('corporate_company_id');
        $settings = CorporateInvoiceSetting::current();

        return view('corporate.quotations.create', [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'programmeOptions' => $settings->programmeOptionsList(),
            'durationOptions' => $settings->durationOptionsList(),
            'driverCountOptions' => $settings->driverCountOptionsList(),
            'serviceOptions' => $settings->serviceOptionsList(),
        ]);
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

        // See the matching comment in CorporateInvoiceController::show() -
        // ActivityLog entries are matched by number (not a foreign key),
        // ordered by id rather than created_at so same-second entries
        // still come out newest-first.
        $activityLogs = ActivityLog::where('description', 'like', "%{$corporateQuotation->quotation_number}%")
            ->orderByDesc('id')
            ->get();

        return view('corporate.quotations.show', [
            'quotation' => $corporateQuotation,
            'activityLogs' => $activityLogs,
            'settings' => CorporateInvoiceSetting::current(),
        ]);
    }

    /**
     * Delete the quotation. Blocked once it's been converted to an
     * invoice - the invoice would otherwise be left referencing a
     * quotation that no longer exists.
     */
    public function destroy(CorporateQuotation $corporateQuotation): RedirectResponse
    {
        if ($corporateQuotation->status === 'converted') {
            return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-already-converted-cannot-delete');
        }

        $number = $corporateQuotation->quotation_number;
        $companyId = $corporateQuotation->corporate_company_id;
        $companyName = $corporateQuotation->company->name;
        $corporateQuotation->delete();

        ActivityLog::record("Deleted corporate quotation {$number} for {$companyName}");

        return Redirect::route('corporate-companies.show', $companyId)->with('status', 'quotation-deleted');
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

    /**
     * Download the quotation as a PDF.
     */
    public function pdf(CorporateQuotation $corporateQuotation): Response
    {
        $corporateQuotation->load(['company', 'items']);

        return $this->buildPdf($corporateQuotation)->download("{$corporateQuotation->quotation_number}.pdf");
    }

    /**
     * Email the quotation PDF to the given address.
     */
    public function email(Request $request, CorporateQuotation $corporateQuotation): RedirectResponse
    {
        $recipientEmail = $request->validate(['recipient_email' => ['required', 'email']])['recipient_email'];
        $corporateQuotation->load(['company', 'items']);

        $pdfContent = $this->buildPdf($corporateQuotation)->output();

        Mail::to($recipientEmail)->send(new CorporateQuotationMail($corporateQuotation, $pdfContent));

        ActivityLog::record("Emailed corporate quotation {$corporateQuotation->quotation_number} to {$recipientEmail}");

        return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-emailed');
    }

    /**
     * Send the quotation PDF to the company's phone number over WhatsApp.
     * The PDF is stored on the public disk at an unguessable path so
     * Twilio can fetch it by URL - nothing links or lists this path.
     */
    public function whatsapp(WhatsAppService $whatsapp, CorporateQuotation $corporateQuotation): RedirectResponse
    {
        $corporateQuotation->load(['company', 'items']);

        if (! $whatsapp->isConfigured()) {
            return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-whatsapp-not-configured');
        }

        if (! $corporateQuotation->company->phone) {
            return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-whatsapp-no-phone');
        }

        $path = "corporate/quotations/{$corporateQuotation->quotation_number}-".Str::random(40).'.pdf';
        Storage::disk('public')->put($path, $this->buildPdf($corporateQuotation)->output());
        $url = Storage::disk('public')->url($path);

        $sent = $whatsapp->sendDocument(
            $corporateQuotation->company->phone,
            $url,
            "Quotation {$corporateQuotation->quotation_number} from Classic Driving School — Total: ₦".number_format($corporateQuotation->total(), 0)
        );

        if (! $sent) {
            return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-whatsapp-failed');
        }

        ActivityLog::record("Sent corporate quotation {$corporateQuotation->quotation_number} to {$corporateQuotation->company->name} via WhatsApp");

        return Redirect::route('corporate-quotations.show', $corporateQuotation)->with('status', 'quotation-whatsapp-sent');
    }

    private function buildPdf(CorporateQuotation $corporateQuotation): PdfDocument
    {
        return Pdf::loadView('corporate.quotations.pdf', [
            'quotation' => $corporateQuotation,
            'settings' => CorporateInvoiceSetting::current(),
            'logoDataUri' => CorporateInvoiceSetting::logoDataUri(),
        ]);
    }
}
