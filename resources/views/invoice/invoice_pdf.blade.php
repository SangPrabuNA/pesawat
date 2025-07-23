<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .main-table th, .main-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .main-table thead th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }
        .header-table td {
            padding: 0;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 36px;
            font-weight: bold;
            color: #555;
        }
        .company-details {
            text-align: right;
        }
        .company-details h3 {
            margin: 0;
            font-size: 18px;
        }
        .bill-to {
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .invoice-meta {
            text-align: right;
        }
        .summary-table {
            width: 40%;
            float: right;
            margin-top: 20px;
        }
        .summary-table td {
            padding: 5px 8px;
        }
        .total {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td>
                <div class="invoice-title">INVOICE</div>
            </td>
            <td class="company-details">
                <h3>AirNav Indonesia</h3>
                <p>
                    Gedung AirNav Indonesia Head Office<br>
                    Jl. Ir. H. Juanda, Tangerang, Banten<br>
                    Indonesia
                </p>
            </td>
        </tr>
    </table>

    <table class="bill-to">
        <tr>
            <td>
                <strong>BILL TO</strong><br>
                {{ $invoice->airline }}
            </td>
            <td class="invoice-meta">
                <strong>Invoice #</strong> {{ $invoice->id }}<br>
                <strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format('F j, Y') }}<br>
                <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->addDays(30)->format('F j, Y') }}
            </td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>DESCRIPTION</th>
                <th>QTY</th>
                <th>RATE</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Biaya {{ $invoice->charge_type }} untuk Pelayanan {{ $invoice->service_type }}
                    <br>
                    <small>
                        Flight: {{ $invoice->flight_number }} |
                        Reg: {{ $invoice->registration }} |
                        Rute: {{ $invoice->route }} |
                        Waktu: {{ \Carbon\Carbon::parse($invoice->actual_time)->format('d M Y, H:i') }}
                    </small>
                </td>
                <td style="text-align: center;">{{ $invoice->billed_hours }}</td>
                <td style="text-align: right;">{{ number_format($invoice->base_rate, 2) }}</td>
                <td style="text-align: right;">{{ number_format($invoice->base_charge, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Subtotal</strong></td>
            <td style="text-align: right;">{{ number_format($invoice->base_charge, 2) }}</td>
        </tr>
        @if($invoice->ppn_charge > 0)
        <tr>
            <td><strong>PPN (11%)</strong></td>
            <td style="text-align: right;">{{ number_format($invoice->ppn_charge, 2) }}</td>
        </tr>
        @endif
        <tr class="total">
            <td><strong>TOTAL</strong></td>
            <td style="text-align: right;"><strong>{{ number_format($invoice->total_charge, 2) }} {{ $invoice->currency }}</strong></td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="footer">
        <p><strong>Terima kasih atas bisnis Anda!</strong></p>
        <p>Pembayaran dapat dilakukan melalui transfer ke rekening yang ditunjuk.</p>
    </div>

</body>
</html>
