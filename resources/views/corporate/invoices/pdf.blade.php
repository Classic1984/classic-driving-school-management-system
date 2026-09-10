<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        table { border-collapse: collapse; }
        .header-table { width: 100%; border-bottom: 3px solid #000000; padding-bottom: 10px; margin-bottom: 16px; }
        .company-name { font-size: 22px; font-weight: bold; color: #000000; margin: 0; }
        .tagline { color: #6b7280; margin: 4px 0 0; font-size: 11px; }
        .slogan { color: #b45309; font-style: italic; margin: 2px 0 0; font-size: 11px; }
        .invoice-title { font-size: 30px; font-weight: bold; color: #000000; text-align: right; margin: 0; }
        .meta-table { width: 100%; font-size: 11px; margin-top: 6px; }
        .meta-table td { padding: 1px 0; text-align: right; }
        .meta-label { font-weight: bold; padding-right: 6px; }
        .boxes-table { width: 100%; margin-top: 16px; }
        .boxes-table td { width: 50%; vertical-align: top; padding: 0 6px; }
        .boxes-table td:first-child { padding-left: 0; }
        .boxes-table td:last-child { padding-right: 0; }
        .box-header { background: #000000; color: #fbbf24; font-weight: bold; font-size: 11px; padding: 6px 10px; }
        .box-body { border: 1px solid #fde68a; border-top: none; padding: 10px; font-size: 11px; }
        .box-body .name { font-size: 14px; font-weight: bold; color: #111827; }
        .items-table { width: 100%; margin-top: 16px; border: 1px solid #fde68a; }
        .items-table th { background: #000000; color: #fbbf24; text-align: left; padding: 6px 10px; font-size: 11px; }
        .items-table td { border-top: 1px solid #fef3c7; padding: 6px 10px; font-size: 11px; }
        .items-table .num { text-align: right; }
        .total-table { width: 50%; margin-left: 50%; margin-top: 12px; border: 1px solid #fde68a; }
        .total-table td { padding: 8px 10px; font-size: 12px; }
        .total-label { background: #fffbeb; color: #000000; font-weight: bold; }
        .total-value { background: #000000; color: #fbbf24; font-weight: bold; font-size: 16px; text-align: right; }
        .words { margin-top: 10px; font-size: 11px; }
        .coverage-table { width: 100%; margin-top: 16px; border: 1px solid #fde68a; }
        .coverage-table td { padding: 6px 10px; font-size: 11px; width: 50%; }
        .signature-box { width: 45%; margin-top: 16px; }
        .signature-img { height: 50px; }
        .signature-line { border-top: 1px solid #9ca3af; margin-top: 24px; padding-top: 4px; }
        .footer { border-top: 2px solid #000000; margin-top: 24px; padding-top: 8px; font-size: 10px; color: #000000; width: 100%; }
        .footer td { padding: 2px 0; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <p class="company-name">{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
                @if ($settings->tagline)
                    <p class="tagline">{{ $settings->tagline }}</p>
                @endif
                @if ($settings->slogan)
                    <p class="slogan">{{ $settings->slogan }}</p>
                @endif
            </td>
            <td style="width: 40%;">
                <p class="invoice-title">INVOICE</p>
                <table class="meta-table">
                    <tr><td class="meta-label">Invoice No:</td><td>{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="meta-label">Date:</td><td>{{ $invoice->invoice_date->format('d F Y') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="boxes-table">
        <tr>
            <td>
                <div class="box-header">BILL TO</div>
                <div class="box-body">
                    <p class="name">{{ $invoice->company->name }}</p>
                    <p>{{ implode(', ', array_filter([$invoice->company->address, $invoice->company->city])) }}</p>
                </div>
            </td>
            <td>
                @if ($invoice->programme_name || $invoice->duration_label || $invoice->participant_count)
                    <div class="box-header">TRAINING DETAILS</div>
                    <div class="box-body">
                        @if ($invoice->programme_name)
                            <p><strong>Course:</strong> {{ $invoice->programme_name }}</p>
                        @endif
                        @if ($invoice->participant_count)
                            <p><strong>Participant:</strong> {{ $invoice->participant_count }} {{ Str::plural('Driver', $invoice->participant_count) }}</p>
                        @endif
                        @if ($invoice->duration_label)
                            <p><strong>Duration:</strong> {{ $invoice->duration_label }}</p>
                        @endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width: 60px;">Qty</th>
                <th style="width: 100px;">Unit Price (₦)</th>
                <th style="width: 100px;">Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td class="num">{{ number_format((float) $item->unit_price, 0) }}</td>
                    <td class="num">{{ number_format($item->amount(), 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="total-table">
        <tr>
            <td class="total-label">TOTAL AMOUNT DUE</td>
            <td class="total-value">₦{{ number_format($invoice->total(), 0) }}</td>
        </tr>
    </table>

    <p class="words"><strong>Amount in Words:</strong> {{ $invoice->totalInWords() }}</p>

    @if (! empty($invoice->courseCoverageList()))
        <table class="coverage-table">
            <tr><td colspan="2" class="box-header">COURSE COVERAGE</td></tr>
            @foreach (array_chunk($invoice->courseCoverageList(), 2) as $pair)
                <tr>
                    <td>{{ $pair[0] }}</td>
                    <td>{{ $pair[1] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="boxes-table" style="margin-top: 16px;">
        <tr>
            @if (! empty($settings->paymentTermsList()))
                <td>
                    <div class="box-header">PAYMENT TERMS</div>
                    <div class="box-body">
                        <ul style="margin: 0; padding-left: 14px;">
                            @foreach ($settings->paymentTermsList() as $term)
                                <li>{{ $term }}</li>
                            @endforeach
                        </ul>
                    </div>
                </td>
            @endif
            <td>
                <div class="box-header">PAYMENT DETAILS</div>
                <div class="box-body">
                    @if ($settings->bank_name)
                        <p><strong>Bank Name:</strong> {{ $settings->bank_name }}</p>
                    @endif
                    @if ($settings->bank_account_name)
                        <p><strong>Account Name:</strong> {{ $settings->bank_account_name }}</p>
                    @endif
                    @if ($settings->bank_account_number)
                        <p><strong>Account Number:</strong> {{ $settings->bank_account_number }}</p>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="signature-box">
        <div class="box-header">DIRECTOR'S SIGNATURE</div>
        <div class="box-body" style="text-align: center;">
            @if ($signatureDataUri)
                <img class="signature-img" src="{{ $signatureDataUri }}">
            @endif
            <div class="signature-line">
                <strong>Director</strong><br>
                {{ $settings->company_name ?: 'Classic Driving School' }}
            </div>
        </div>
    </div>

    <table class="footer">
        <tr>
            @if ($settings->phone)
                <td>{{ $settings->phone }}</td>
            @endif
            @if ($settings->website)
                <td style="text-align: center;">{{ $settings->website }}</td>
            @endif
            @if ($settings->address)
                <td style="text-align: right;">{{ $settings->address }}</td>
            @endif
        </tr>
        @if ($settings->slogan)
            <tr><td colspan="3" style="text-align: center; font-style: italic; padding-top: 4px;">{{ $settings->slogan }}</td></tr>
        @endif
    </table>
</body>
</html>
