<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 10px;
            margin: 20px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: middle;
        }
        .header-table .logo {
            width: 150px;
        }
        .header-table .title {
            text-align: center;
            line-height: 1.4;
            font-size: 11px;
        }
        .header-table .invoice-info {
            text-align: right;
            font-size: 10px;
        }
        .section {
            margin-top: 15px;
        }
        .info-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .charges-table, .charges-table th, .charges-table td {
            border: 1px solid black;
            text-align: center;
            padding: 5px;
        }
        .charges-table th {
            background: #f2f2f2;
        }
        .charges-table .text-left {
            text-align: left;
        }
        .charges-table .text-right {
            text-align: right;
        }
        .footer-table {
            margin-top: 30px;
        }
        .footer-table td {
            vertical-align: top;
        }
        .footer-table .signature {
            text-align: right;
        }
        .footer-table .signature-space {
            height: 50px;
        }
        .footer-table .cc {
            font-size: 9px;
            line-height: 1.5;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        // Mengambil gambar dari public path dan mengubahnya ke Base64
        $logoPath = public_path('images/logo.jpg');
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/jpeg;base64,' . $logoData;
        } else {
            $logoSrc = '';
        }
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 5%;">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="logo">
                @endif
            </td>
            <td style="width: 50%;" class="title">
                <span class="bold">AIRNAV INDONESIA</span><br>
                PERUM LEMBAGA PENYELENGGARA PELAYANAN NAVIGASI PENERBANGAN INDONESIA<br>
                <span class="bold">ADVANCED/EXTENDED CHARGES</span>
            </td>
            <td style="width: 25%;" class="invoice-info">
                <span class="bold">NO</span>: {{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}/{{ $invoice->airport->iata_code ?? '' }}/{{ \Carbon\Carbon::parse($invoice->created_at)->format('m/Y') }}<br>
                <span class="bold">TANGGAL/DATE</span>: {{ \Carbon\Carbon::parse($invoice->created_at)->format('l, d F Y') }}
            </td>
        </tr>
    </table>

    <div class="section">
        <table class="info-table">
            <tr>
                <td width="30%">AIRLINE/Airline</td>
                <td>: {{ $invoice->airline }}</td>
            </tr>
            <tr>
                <td>GROUND HANDLING</td>
                <td>: {{ $invoice->ground_handling ?? $invoice->airline }}</td>
            </tr>
            <tr>
                <td>NOMOR PENERBANGAN/Flight Number</td>
                <td>: {{ $invoice->flight_number }} {{ $invoice->flight_number_2 ? '/ '.$invoice->flight_number_2 : '' }}</td>
            </tr>
            <tr>
                <td>REGISTRASI/Registration</td>
                <td>: {{ $invoice->registration }}</td>
            </tr>
            <tr>
                <td>JENIS PESAWAT/Type of Aircraft</td>
                <td>: {{ $invoice->aircraft_type }}</td>
            </tr>
            <tr>
                <td>DATANG DARI/Arrival From</td>
                <td>: {{ $invoice->departure_airport }}</td>
            </tr>
            <tr>
                <td>BERANGKAT KE/Departure To</td>
                <td>: {{ $invoice->arrival_airport }}</td>
            </tr>
            <tr>
                <td>TANGGAL & WAKTU DATANG/Arrival Date & Time</td>
                <td>: {{ $invoice->movement_type == 'Arrival' ? \Carbon\Carbon::parse($invoice->actual_time)->format('Y-m-d') : '-' }} &nbsp;&nbsp;&nbsp; ATA/Actual Time Arrival: {{ $invoice->movement_type == 'Arrival' ? \Carbon\Carbon::parse($invoice->actual_time)->format('H:i:s') : '-' }}</td>
            </tr>
            <tr>
                <td>TANGGAL & WAKTU BERANGKAT/Departure Date & Time</td>
                <td>: {{ $invoice->movement_type == 'Departure' ? \Carbon\Carbon::parse($invoice->actual_time)->format('Y-m-d') : '-' }} &nbsp;&nbsp;&nbsp; ATD/Actual Time Departure: {{ $invoice->movement_type == 'Departure' ? \Carbon\Carbon::parse($invoice->actual_time)->format('H:i:s') : '-' }}</td>
            </tr>
            <tr>
                <td>DIBAYAR OLEH/Fee Will Be Paid By</td>
                <td>: {{ $invoice->airline }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <table class="charges-table">
            <thead>
                <tr>
                    <th>ADVANCED/EXTENDED CHARGES</th>
                    <th>START</th>
                    <th>END</th>
                    <th>DURATION</th>
                    <th>RATE</th>
                    <th>TOTAL</th>
                    <th>PPN/VAT 11%</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left">{{ strtoupper($invoice->charge_type) }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->charge_type == 'Extend' ? $invoice->operational_hour_end : $invoice->actual_time)->format('H:i:s') }}</td>
                    <td>{{ \Carbon\Carbon::parse($invoice->charge_type == 'Extend' ? $invoice->actual_time : $invoice->operational_hour_start)->format('H:i:s') }}</td>
                    <td>{{ floor($invoice->duration_minutes / 60) }}:{{ str_pad($invoice->duration_minutes % 60, 2, '0', STR_PAD_LEFT) }}:00</td>
                    <td class="text-right">{{ $invoice->currency == 'IDR' ? 'Rp' : '$' }} {{ number_format($invoice->base_rate, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $invoice->currency == 'IDR' ? 'Rp' : '$' }} {{ number_format($invoice->base_charge, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $invoice->currency == 'IDR' ? 'Rp' : '$' }} {{ number_format($invoice->ppn_charge, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $invoice->currency == 'IDR' ? 'Rp' : '$' }} {{ number_format($invoice->base_charge + $invoice->ppn_charge, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="footer-table">
        <tr>
            <td style="width: 70%;" class="cc">
                CC:<br>
                1. Customer<br>
                2. Finance<br>
                3. File
            </td>
            <td style="width: 30%;" class="signature">
                Petugas Official AIRNAV INDONESIA
                <div class="signature-space"></div>
                ( .......................... )
            </td>
        </tr>
    </table>

</body>
</html>
