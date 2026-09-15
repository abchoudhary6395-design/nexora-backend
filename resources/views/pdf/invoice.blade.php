<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #171A2C; }
        .header { border-bottom: 1px solid #E1E4EC; padding-bottom: 16px; margin-bottom: 24px; width: 100%; }
        .header td { vertical-align: top; }
        .brand { font-size: 18px; font-weight: bold; color: #4F5EFF; }
        .invoice-id { font-size: 16px; font-weight: bold; text-align: right; }
        .parties { width: 100%; margin-bottom: 24px; }
        .parties td { vertical-align: top; width: 50%; }
        .label { text-transform: uppercase; font-size: 9px; color: #6E7490; margin-bottom: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { text-align: left; font-size: 9px; text-transform: uppercase; color: #6E7490; border-bottom: 1px solid #E1E4EC; padding-bottom: 6px; }
        table.items td { padding: 8px 0; border-bottom: 1px solid #E1E4EC; }
        .text-right { text-align: right; }
        .totals { width: 240px; margin-left: auto; }
        .totals td { padding: 4px 0; }
        .grand { font-weight: bold; font-size: 14px; border-top: 1px solid #E1E4EC; padding-top: 8px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td class="brand">Nexora</td>
            <td class="invoice-id">{{ $invoice->invoice_number }}<br><span style="font-size:10px; color:#6E7490;">{{ $invoice->status }}</span></td>
        </tr>
    </table>

    <table class="parties">
        <tr>
            <td>
                <div class="label">Billed To</div>
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->email }}
            </td>
            <td class="text-right">
                <div class="label">Dates</div>
                Issued {{ $invoice->issue_date->format('Y-m-d') }}<br>
                Due {{ $invoice->due_date->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td></tr>
        <tr><td>Discount</td><td class="text-right">-${{ number_format($invoice->discount, 2) }}</td></tr>
        <tr><td>Tax ({{ $invoice->tax_rate }}%)</td><td class="text-right">${{ number_format($invoice->tax_amount, 2) }}</td></tr>
        <tr class="grand"><td>Total Due</td><td class="text-right">${{ number_format($invoice->total, 2) }}</td></tr>
    </table>
</body>
</html>
