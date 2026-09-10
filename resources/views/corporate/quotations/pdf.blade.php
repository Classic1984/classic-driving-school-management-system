<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1f2937; }
        table { border-collapse: collapse; }
        .header { text-align: center; padding-bottom: 10px; border-bottom: 2px solid #fbbf24; }
        .company-name { font-size: 15px; font-weight: bold; letter-spacing: 2px; color: #000000; margin: 0; }
        .quotation-title { font-size: 40px; font-weight: bold; color: #b45309; margin: 4px 0 0; }
        .slogan { color: #b45309; font-style: italic; margin: 2px 0 0; font-size: 11px; }
        .meta-table { width: 100%; font-size: 11px; margin-top: 10px; }
        .meta-table td { padding: 1px 0; }
        .meta-label { font-weight: bold; color: #b45309; padding-right: 6px; }
        .boxes-table { width: 100%; margin-top: 16px; }
        .boxes-table td { width: 50%; vertical-align: top; padding: 0 6px; }
        .boxes-table td:first-child { padding-left: 0; }
        .boxes-table td:last-child { padding-right: 0; }
        .box-header { background: #000000; color: #fbbf24; font-weight: bold; font-size: 11px; padding: 8px 10px; border-radius: 8px 8px 0 0; }
        .box-body { border: 1px solid #fde68a; border-top: none; padding: 10px; font-size: 11px; border-radius: 0 0 8px 8px; }
        .box-body .name { font-size: 14px; font-weight: bold; color: #111827; }
        .items-table { width: 100%; margin-top: 16px; border: 1px solid #fde68a; }
        .items-table th { background: #000000; color: #fbbf24; text-align: left; padding: 6px 10px; font-size: 11px; }
        .items-table td { border-top: 1px solid #fef3c7; padding: 6px 10px; font-size: 11px; }
        .items-table .num { text-align: right; }
        .total-table { width: 50%; margin-left: 50%; margin-top: 12px; }
        .total-table td { padding: 8px 10px; font-size: 12px; }
        .total-label { background: #fbbf24; color: #000000; font-weight: bold; border-radius: 999px 0 0 999px; }
        .total-value { background: #000000; color: #fbbf24; font-weight: bold; font-size: 16px; text-align: right; border-radius: 0 999px 999px 0; }
        .coverage-table { width: 100%; margin-top: 16px; border: 1px solid #fde68a; }
        .coverage-table td { padding: 6px 10px; font-size: 11px; width: 50%; }
        .notes { margin-top: 16px; font-size: 11px; color: #4b5563; }
        .footer { border-top: 2px solid #000000; margin-top: 24px; padding-top: 8px; font-size: 10px; color: #000000; width: 100%; }
        .footer td { padding: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">{{ strtoupper($settings->company_name ?: 'CLASSIC DRIVING SCHOOL') }}</p>
        <p class="quotation-title">QUOTATION</p>
        @if ($settings->slogan)
            <p class="slogan">{{ $settings->slogan }}</p>
        @endif
    </div>

    <table class="meta-table">
        <tr><td class="meta-label">Quotation No:</td><td>{{ $quotation->quotation_number }}</td></tr>
        <tr><td class="meta-label">Date:</td><td>{{ $quotation->issue_date->format('d F Y') }}</td></tr>
        @if ($quotation->valid_until)
            <tr><td class="meta-label">Valid Until:</td><td>{{ $quotation->valid_until->format('d F Y') }}</td></tr>
        @endif
    </table>

    <table class="boxes-table">
        <tr>
            <td>
                <div class="box-header">QUOTED TO</div>
                <div class="box-body">
                    <p class="name">{{ $quotation->company->name }}</p>
                    <p>{{ implode(', ', array_filter([$quotation->company->address, $quotation->company->city])) }}</p>
                </div>
            </td>
            <td>
                @if ($quotation->programme_name || $quotation->duration_label || $quotation->participant_count)
                    <div class="box-header">TRAINING DETAILS</div>
                    <div class="box-body">
                        @if ($quotation->programme_name)
                            <p><strong>Course:</strong> {{ $quotation->programme_name }}</p>
                        @endif
                        @if ($quotation->participant_count)
                            <p><strong>Participant:</strong> {{ $quotation->participant_count }} {{ Str::plural('Driver', $quotation->participant_count) }}</p>
                        @endif
                        @if ($quotation->duration_label)
                            <p><strong>Duration:</strong> {{ $quotation->duration_label }}</p>
                        @endif
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 30px;">S/N</th>
                <th>Description</th>
                <th style="width: 60px;">Qty</th>
                <th style="width: 100px;">Unit Price (₦)</th>
                <th style="width: 100px;">Amount (₦)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
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
            <td class="total-label">TOTAL</td>
            <td class="total-value">₦{{ number_format($quotation->total(), 0) }}</td>
        </tr>
    </table>

    @if (! empty($quotation->courseCoverageList()))
        <table class="coverage-table">
            <tr><td colspan="2" class="box-header">COURSE COVERAGE</td></tr>
            @foreach (array_chunk($quotation->courseCoverageList(), 2) as $pair)
                <tr>
                    <td>{{ $pair[0] }}</td>
                    <td>{{ $pair[1] ?? '' }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if ($quotation->notes)
        <p class="notes">{{ $quotation->notes }}</p>
    @endif

    @if ($settings->phone || $settings->website || $settings->address)
        <table class="footer">
            <tr>
                @if ($settings->address)
                    <td>{{ $settings->address }}</td>
                @endif
                @if ($settings->phone)
                    <td style="text-align: center;">{{ $settings->phone }}</td>
                @endif
                @if ($settings->website)
                    <td style="text-align: right;">{{ $settings->website }}</td>
                @endif
            </tr>
        </table>
    @endif
</body>
</html>
