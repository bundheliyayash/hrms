<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #3498db; padding-bottom: 20px; }
        .logo { color: #3498db; font-size: 28px; font-weight: bold; }
        .invoice-title { text-align: right; text-transform: uppercase; color: #7f8c8d; font-size: 32px; font-weight: normal; margin: 0; }
        .meta { display: table; width: 100%; margin-top: 30px; margin-bottom: 30px; }
        .meta-group { display: table-cell; vertical-align: top; width: 50%; }
        .label { text-transform: uppercase; font-size: 10px; font-weight: bold; color: #95a5a6; margin-bottom: 5px; }
        .data { font-size: 14px; font-weight: bold; }
        .billing-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .billing-table th { background: #2c3e50; color: #fff; text-align: left; padding: 8px; font-size: 12px; }
        .billing-table td { padding: 10px 8px; border-bottom: 1px solid #ecf0f1; font-size: 13px; }
        .totals { float: right; width: 300px; margin-top: 30px; }
        .total-row { display: table; width: 100%; margin-bottom: 5px; }
        .total-label { display: table-cell; width: 60%; font-size: 14px; color: #7f8c8d; }
        .total-value { display: table-cell; width: 40%; text-align: right; font-size: 14px; font-weight: bold; }
        .grand-total { border-top: 1px solid #333; padding-top: 10px; margin-top: 10px; font-size: 18px !important; color: #3498db !important; }
        .footer { margin-top: 100px; font-size: 12px; color: #bdc3c7; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        .stamp { position: absolute; top: 150px; right: 50px; border: 5px solid #e74c3c; color: #e74c3c; padding: 10px 20px; border-radius: 10px; font-size: 24px; font-weight: bold; text-transform: uppercase; opacity: 0.3; transform: rotate(-20deg); }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div style="display: table-cell;">
                <div class="logo">CLEAN SHEEN</div>
                <div style="font-size: 11px; margin-top: 5px;">Quality Cleaning Services<br>Sector 5, Business Park, Mumbai</div>
            </div>
            <div style="display: table-cell; text-align: right;">
                <h1 class="invoice-title">Invoice</h1>
                <div class="data">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        @if($invoice->status == 'paid')
            <div class="stamp">PAID</div>
        @endif

        <div class="meta">
            <div class="meta-group">
                <div class="label">Bill To</div>
                <div class="data">{{ $invoice->client->name }}</div>
                <div style="font-size: 12px; font-weight: normal; margin-top: 4px;">
                    {{ $invoice->client->contact_person }}<br>
                    {{ $invoice->client->email }}<br>
                    {{ $invoice->client->phone }}
                </div>
            </div>
            <div class="meta-group text-right" style="text-align: right;">
                <div class="label">Payment Details</div>
                <div style="font-size: 12px;">
                    Invoice Date: <strong>{{ $invoice->invoice_date->format('d/m/Y') }}</strong><br>
                    Due Date: <strong style="color: #e74c3c;">{{ $invoice->due_date->format('d/m/Y') }}</strong><br>
                    Period: <strong>{{ $invoice->billing_period_start->format('d/m/y') }} - {{ $invoice->billing_period_end->format('d/m/y') }}</strong>
                </div>
            </div>
        </div>

        <table class="billing-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th>Worker Name</th>
                    <th>Site Details</th>
                    <th style="text-align: center; width: 10%;">Units</th>
                    <th style="text-align: right; width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->lineItems as $item)
                <tr>
                    <td>{{ $item->service_date->format('d/m/y') }}</td>
                    <td>{{ $item->worker_name }}</td>
                    <td>{{ $item->site->site_name }}</td>
                    <td style="text-align: center;">1.0</td>
                    <td style="text-align: right;">₹{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">₹{{ number_format($invoice->gross_amount, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Discount:</div>
                <div class="total-value text-danger">- ₹{{ number_format($invoice->discount_amount, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">GST {{ $invoice->tax_percentage }}%:</div>
                <div class="total-value">₹{{ number_format($invoice->tax_amount, 2) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label" style="color: #3498db; font-weight: bold;">TOTAL DUE:</div>
                <div class="total-value" style="color: #3498db;">₹{{ number_format($invoice->net_amount, 2) }}</div>
            </div>
        </div>

        <div style="clear: both; margin-top: 50px;">
            <div class="label">Terms & Notes</div>
            <div style="font-size: 11px; color: #7f8c8d;">
                {{ $invoice->notes ?? 'Please make payment on or before the due date. Quote the invoice number in your transfer reference for faster processing. All payments should be made in favor of CLEAN SHEEN.' }}
            </div>
        </div>

        <div class="footer">
            Generated by Clean Sheen HRMS System on {{ now()->format('d F Y, H:i') }} | This is a computer generated document and does not require a physical signature.
        </div>
    </div>
</body>
</html>
