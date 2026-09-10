<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporateInvoiceRequest;
use App\Mail\CorporateInvoiceMail;
use App\Models\ActivityLog;
use App\Models\CorporateCompany;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
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

class CorporateInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $invoices = CorporateInvoice::with(['company', 'items'])
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        return view('corporate.invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $companies = CorporateCompany::orderBy('name')->get(['id', 'name']);
        $selectedCompanyId = $request->query('corporate_company_id');

        return view('corporate.invoices.create', compact('companies', 'selectedCompanyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCorporateInvoiceRequest $request): RedirectResponse
    {
        $invoice = DB::transaction(function () use ($request) {
            $invoice = CorporateInvoice::create([
                ...$request->safe()->except('items'),
                'created_by' => $request->user()->id,
            ]);

            foreach ($request->validated('items') as $index => $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'sort_order' => $index,
                ]);
            }

            return $invoice;
        });

        ActivityLog::record("Created corporate invoice {$invoice->invoice_number} for {$invoice->company->name}");

        return Redirect::route('corporate-invoices.show', $invoice)->with('status', 'invoice-created');
    }

    /**
     * Display the specified resource.
     */
    public function show(CorporateInvoice $corporateInvoice): View
    {
        $corporateInvoice->load(['company', 'items', 'payments']);
        $settings = CorporateInvoiceSetting::current();

        return view('corporate.invoices.show', ['invoice' => $corporateInvoice, 'settings' => $settings]);
    }

    /**
     * Mark the invoice as sent to the company.
     */
    public function send(Request $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        if ($corporateInvoice->status !== 'pending') {
            return Redirect::back()->with('status', 'invoice-cannot-send');
        }

        $corporateInvoice->update([
            'status' => 'sent',
            'sent_by' => $request->user()->id,
            'sent_at' => now(),
        ]);

        ActivityLog::record("Sent corporate invoice {$corporateInvoice->invoice_number} to {$corporateInvoice->company->name}");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-sent');
    }

    /**
     * Cancel the invoice. Blocked once it's fully paid - undoing a paid
     * invoice is a refund/reversal decision, not a cancellation, and isn't
     * handled here.
     */
    public function cancel(Request $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        if ($corporateInvoice->status === 'paid') {
            return Redirect::back()->with('status', 'invoice-cannot-cancel');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:255'],
        ]);

        $corporateInvoice->update([
            'status' => 'cancelled',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->string('cancellation_reason'),
        ]);

        ActivityLog::record("Cancelled corporate invoice {$corporateInvoice->invoice_number} ({$corporateInvoice->cancellation_reason})");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-cancelled');
    }

    /**
     * Download the invoice as a PDF.
     */
    public function pdf(CorporateInvoice $corporateInvoice): Response
    {
        $corporateInvoice->load(['company', 'items']);

        return $this->buildPdf($corporateInvoice)->download("{$corporateInvoice->invoice_number}.pdf");
    }

    /**
     * Email the invoice PDF to the given address (defaulting to the
     * company's own email, but overridable in case it should go
     * elsewhere for this one invoice).
     */
    public function email(Request $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        $recipientEmail = $request->validate(['recipient_email' => ['required', 'email']])['recipient_email'];
        $corporateInvoice->load(['company', 'items']);

        $pdfContent = $this->buildPdf($corporateInvoice)->output();

        Mail::to($recipientEmail)->send(new CorporateInvoiceMail($corporateInvoice, $pdfContent));

        ActivityLog::record("Emailed corporate invoice {$corporateInvoice->invoice_number} to {$recipientEmail}");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-emailed');
    }

    /**
     * Send the invoice PDF to the company's phone number over WhatsApp.
     * The PDF is stored on the public disk at an unguessable path so
     * Twilio can fetch it by URL - nothing links or lists this path.
     */
    public function whatsapp(WhatsAppService $whatsapp, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        $corporateInvoice->load(['company', 'items']);

        $path = "corporate/invoices/{$corporateInvoice->invoice_number}-".Str::random(40).'.pdf';
        Storage::disk('public')->put($path, $this->buildPdf($corporateInvoice)->output());
        $url = Storage::disk('public')->url($path);

        $sent = $whatsapp->sendDocument(
            $corporateInvoice->company->phone,
            $url,
            "Invoice {$corporateInvoice->invoice_number} from Classic Driving School — Total Due: ₦".number_format($corporateInvoice->total(), 0)
        );

        if (! $sent) {
            return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-whatsapp-failed');
        }

        ActivityLog::record("Sent corporate invoice {$corporateInvoice->invoice_number} to {$corporateInvoice->company->name} via WhatsApp");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'invoice-whatsapp-sent');
    }

    private function buildPdf(CorporateInvoice $corporateInvoice): PdfDocument
    {
        $settings = CorporateInvoiceSetting::current();

        return Pdf::loadView('corporate.invoices.pdf', [
            'invoice' => $corporateInvoice,
            'settings' => $settings,
            'signatureDataUri' => $settings->signatureDataUri(),
        ]);
    }
}
