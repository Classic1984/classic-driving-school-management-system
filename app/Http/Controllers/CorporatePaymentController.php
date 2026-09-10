<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorporatePaymentRequest;
use App\Models\ActivityLog;
use App\Models\CorporateInvoice;
use App\Models\CorporateInvoiceSetting;
use App\Models\CorporatePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
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
        $settings = CorporateInvoiceSetting::current();

        $pdf = Pdf::loadView('corporate.payments.receipt-pdf', ['payment' => $corporatePayment, 'settings' => $settings]);

        return $pdf->download("{$corporatePayment->receipt_number}.pdf");
    }
}
