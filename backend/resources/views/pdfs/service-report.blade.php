<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Service Report #{{ $report->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; }
        .totals td { border: none; padding: 2px 8px; }
        .totals .label { text-align: right; }
        .section { margin-top: 20px; }
        .signature-img { max-width: 220px; max-height: 100px; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>ThermoOps Service Report</h1>
    <p class="muted">Report #{{ $report->id }} &middot; Work Order #{{ $report->work_order_id }} &middot; {{ $report->created_at->toFormattedDateString() }}</p>

    <div class="section">
        <strong>Customer:</strong> {{ $report->customer->name }}<br>
        <strong>Location:</strong> {{ $report->location->name }}, {{ $report->location->address }}<br>
        <strong>Technician:</strong> {{ $report->technician?->name ?? 'Unassigned' }}<br>
        <strong>Service Type:</strong> {{ $report->type }}
    </div>

    <div class="section">
        <table>
            <thead>
                <tr><th>Description</th><th>Type</th><th>Qty</th><th>Unit Price</th><th>Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach ($report->workOrder->lineItems as $lineItem)
                    <tr>
                        <td>{{ $lineItem->description }}</td>
                        <td>{{ ucfirst($lineItem->type) }}</td>
                        <td>{{ $lineItem->quantity }}</td>
                        <td>${{ number_format($lineItem->unit_price, 2) }}</td>
                        <td>${{ number_format($lineItem->subtotal(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td class="label">Subtotal</td><td>${{ number_format($report->subtotal, 2) }}</td></tr>
            <tr><td class="label">Tax</td><td>${{ number_format($report->tax, 2) }}</td></tr>
            <tr><td class="label"><strong>Total</strong></td><td><strong>${{ number_format($report->total, 2) }}</strong></td></tr>
        </table>
    </div>

    <div class="section">
        <strong>Work Performed:</strong>
        <p>{{ $report->workOrder->description }}</p>
    </div>

    @if ($signaturePath)
        <div class="section">
            <strong>Signed:</strong> {{ $report->signed_at?->toFormattedDateString() }}<br>
            <img class="signature-img" src="{{ $signaturePath }}" alt="Signature">
        </div>
    @endif
</body>
</html>
