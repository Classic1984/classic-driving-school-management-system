<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        table { border-collapse: collapse; }
        .header-table { width: 100%; border-bottom: 3px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 16px; }
        .company-name { font-size: 20px; font-weight: bold; color: #1e3a8a; margin: 0; }
        .address { color: #6b7280; margin: 4px 0 0; font-size: 11px; }
        .quotation-title { font-size: 30px; font-weight: bold; color: #1e3a8a; text-align: right; margin: 0; }
        .meta-table { width: 100%; font-size: 11px; margin-top: 6px; }
        .meta-table td { padding: 1px 0; text-align: right; }
        .meta-label { font-weight: bold; padding-right: 6px; }
        .box-header { background: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 11px; padding: 6px 10px; }
        .box-body { border: 1px solid #bfdbfe; border-top: none; padding: 10px; font-size: 11px; }
        .items-table { width: 100%; margin-top: 16px; border: 1px solid #bfdbfe; }
        .items-table th { background: #1e3a8a; color: #ffffff; text-align: left; padding: 6px 10px; font-size: 11px; }
        .items-table td { border-top: 1px solid #dbeafe; padding: 6px 10px; font-size: 11px; }
        .items-table .num { text-align: right; }
        .total-table { width: 50%; margin-left: 50%; margin-top: 12px; border: 1px solid #bfdbfe; }
        .total-table td { padding: 8px 10px; font-size: 12px; }
        .total-label { background: #eff6ff; color: #1e3a8a; font-weight: bold; }
        .total-value { background: #1e3a8a; color: #ffffff; font-weight: bold; font-size: 16px; text-align: right; }
        .coverage-table { width: 100%; margin-top: 16px; border: 1px solid #bfdbfe; }
        .coverage-table td { padding: 6px 10px; font-size: 11px; width: 50%; }
        .notes { margin-top: 16px; font-size: 11px; color: #4b5563; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <p class="company-name">{{ strtoupper($quotation->company->name) }}</p>
                <p class="address">{{ implode(', ', array_filter([$quotation->company->address, $quotation->company->city])) }}</p>
            </td>
            <td style="width: 40%;">
                <p class="quotation-title">QUOTATION</p>
                <table class="meta-table">
                    <tr><td class="meta-label">Quotation No:</td><td>{{ $quotation->quotation_number }}</td></tr>
                    <tr><td class="meta-label">Date:</td><td>{{ $quotation->issue_date->format('d F Y') }}</td></tr>
                    @if ($quotation->valid_until)
                        <tr><td class="meta-label">Valid Until:</td><td>{{ $quotation->valid_until->format('d F Y') }}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

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
            @foreach ($quotation->items as $item)
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
            <td class="total-label">TOTAL</td>
            <td class="total-value">₦{{ number_format($quotation->total(), 0) }}</td>
        </tr>
    </table>

    @if (! empty($quotation->courseCoverageList()))
        <table class="coverage-table">
            <tr><td colspan="2" class="box-header" style="color:#fff;">COURSE COVERAGE</td></tr>
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
</body>
</html>
