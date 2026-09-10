<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporatePaymentRequest;
use App\Mail\CorporateReceiptMail;
use App\Models\ActivityLog;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\CorporatePayment;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PdfDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CorporatePaymentController extends Controller
{
    /**
     * Record a payment against an invoice.
     */
    public function store(StoreCorporatePaymentRequest $request, CorporateInvoice $corporateInvoice): RedirectResponse
    {
        $payment = $corporateInvoice->payments()->create([
            ...$request->validated(),
            'recorded_by' => $request->user()->id,
        ]);

        // A payment can bring the invoice to fully paid without anyone
        // separately marking it so - status only ever moves forward here,
        // a cancelled invoice being paid against is not this method's
        // concern (the invoice view hides the form once cancelled).
        if ($corporateInvoice->fresh('payments')->amountPaid() >= $corporateInvoice->total()) {
            $corporateInvoice->update(['status' => 'paid']);
        }

        ActivityLog::record('Recorded a payment of ₦'.number_format((float) $payment->amount, 2)." for corporate invoice {$corporateInvoice->invoice_number}");

        return Redirect::route('corporate-invoices.show', $corporateInvoice)->with('status', 'payment-recorded');
    }

    /**
     * Display the printable receipt for a payment.
     */
    public function receipt(CorporatePayment $corporatePayment): View
    {
        $corporatePayment->load('invoice.company');
        $settings = CorporateInvoiceSetting::current();

        return view('corporate.payments.receipt', ['payment' => $corporatePayment, 'settings' => $settings]);
    }

    /**
     * Download the payment's receipt as a PDF.
     */
    public function receiptPdf(CorporatePayment $corporatePayment): Response
    {
        $corporatePayment->load('invoice.company');

        return $this->buildPdf($corporatePayment)->download("{$corporatePayment->receipt_number}.pdf");
    }

    /**
     * Email the receipt PDF to the given address.
     */
    public function receiptEmail(Request $request, CorporatePayment $corporatePayment): RedirectResponse
    {
        $recipientEmail = $request->validate(['recipient_email' => ['required', 'email']])['recipient_email'];
        $corporatePayment->load('invoice.company');

        $pdfContent = $this->buildPdf($corporatePayment)->output();

        Mail::to($recipientEmail)->send(new CorporateReceiptMail($corporatePayment, $pdfContent));

        ActivityLog::record("Emailed corporate receipt {$corporatePayment->receipt_number} to {$recipientEmail}");

        return Redirect::route('corporate-payments.receipt', $corporatePayment)->with('status', 'receipt-emailed');
    }

    /**
     * Send the receipt PDF to the company's phone number over WhatsApp.
     * The PDF is stored on the public disk at an unguessable path so
     * Twilio can fetch it by URL - nothing links or lists this path.
     */
    public function receiptWhatsapp(WhatsAppService $whatsapp, CorporatePayment $corporatePayment): RedirectResponse
    {
        $corporatePayment->load('invoice.company');

        if (! $whatsapp->isConfigured()) {
            return Redirect::route('corporate-payments.receipt', $corporatePayment)->with('status', 'receipt-whatsapp-not-configured');
        }

        if (! $corporatePayment->invoice->company->phone) {
            return Redirect::route('corporate-payments.receipt', $corporatePayment)->with('status', 'receipt-whatsapp-no-phone');
        }

        $path = "corporate/receipts/{$corporatePayment->receipt_number}-".Str::random(40).'.pdf';
        Storage::disk('public')->put($path, $this->buildPdf($corporatePayment)->output());
        $url = Storage::disk('public')->url($path);

        $sent = $whatsapp->sendDocument(
            $corporatePayment->invoice->company->phone,
            $url,
            "Payment Receipt {$corporatePayment->receipt_number} from Classic Driving School — Amount Paid: ₦".number_format((float) $corporatePayment->amount, 0)
        );

        if (! $sent) {
            return Redirect::route('corporate-payments.receipt', $corporatePayment)->with('status', 'receipt-whatsapp-failed');
        }

        ActivityLog::record("Sent corporate receipt {$corporatePayment->receipt_number} to {$corporatePayment->invoice->company->name} via WhatsApp");

        return Redirect::route('corporate-payments.receipt', $corporatePayment)->with('status', 'receipt-whatsapp-sent');
    }

    private function buildPdf(CorporatePayment $corporatePayment): PdfDocument
    {
        $settings = CorporateInvoiceSetting::current();

        return Pdf::loadView('corporate.payments.receipt-pdf', ['payment' => $corporatePayment, 'settings' => $settings]);
    }
}
