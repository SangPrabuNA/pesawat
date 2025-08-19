<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 9px;
            margin: 15px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            vertical-align: top;
        }
        .text-strong { font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .va-middle { vertical-align: middle; }
        .page-break { page-break-after: always; }

        /* Header */
        .header-table .logo { width: 80px; }
        .header-table .title {
            text-align: center;
            line-height: 1.3;
            font-size: 11px;
            padding: 0 10px;
        }
        .header-table .invoice-details-box {
            border: 1px solid #000;
            padding: 5px;
        }
        .invoice-details-box table td {
            padding: 1px;
            font-size: 10px;
        }

        /* Info Utama */
        .main-info-table { margin-top: 10px; }
        .main-info-table > tbody > tr > td {
            padding: 2px 0;
            font-size: 10px;
        }
        .main-info-table .label-col { width: 30%; }

        /* Tabel Biaya - Updated Structure */
        .charges-table { margin-top: 10px; }
        .charges-table th, .charges-table td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
        }
        .charges-table thead th {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .charges-table .text-left { text-align: left; }
        .charges-table .currency-col { width: 8%; }
        .charges-table .amount-col { width: 12%; }

        /* Total Section */
        .total-section { margin-top: 10px; }
        .total-section table{ width: 100%; }
        .total-section td {
            border: 1px solid black;
            padding: 4px;
            font-size: 9px;
            text-align: right;
        }
        .total-section .label-col {
            text-align: left;
            width: 60%;
            padding-left: 8px;
        }

        /* Footer */
        .note-section { margin-top: 5px; font-size: 8px; }
        .bank-details-box {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 5px;
        }
        .bank-details-box .bank-logo { width: 100px; }
        .footer-section { margin-top: 20px; }
        .footer-section .signature-block {
            text-align: center;
            padding-left: 65%;
        }
        .footer-section .signature-space { height: 40px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoSrc = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $bankLogoPath = public_path('images/BRI.png');
        $bankLogoSrc = file_exists($bankLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bankLogoPath)) : '';
    @endphp

    <!-- BAGIAN HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                @if($logoSrc) <img src="{{ $logoSrc }}" class="logo"> @endif
            </td>
            <td style="width: 55%;" class="title va-middle">
                <span class="text-strong">AIRNAV INDONESIA</span><br>
                PERUM LEMBAGA PENYELENGGARA PELAYANAN NAVIGASI PENERBANGAN INDONESIA<br>
                <span class="text-strong">ADVANCED/EXTENDED CHARGES</span>
            </td>
            <td style="width: 30%;">
                <div class="invoice-details-box">
                    <table>
                        <tr>
                            <td><span class="text-strong">NO</span></td>
                            <td>: {{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}/{{ $invoice->airport->iata_code ?? '' }}/{{ \Carbon\Carbon::parse($invoice->created_at)->format('m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><span class="text-strong">TANGGAL/DATE</span></td>
                            <td>: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d F Y') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- BAGIAN INFORMASI UTAMA -->
    <table class="main-info-table">
        <tr><td class="label-col">AIRLINE/Airline</td><td>: {{ $invoice->airline }}</td></tr>
        <tr><td class="label-col">GROUND HANDLING</td><td>: {{ $invoice->ground_handling ?? $invoice->airline }}</td></tr>
        <tr><td class="label-col">NOMOR PENERBANGAN/Flight Number</td><td>: {{ $invoice->flight_number }} {{ $invoice->flight_number_2 ? '/ '.$invoice->flight_number_2 : '' }}</td></tr>
        <tr><td class="label-col">REGISTRASI/Registration</td><td>: {{ $invoice->registration }}</td></tr>
        <tr><td class="label-col">JENIS PESAWAT/Type of Aircraft</td><td>: {{ $invoice->aircraft_type }}</td></tr>
        <tr><td class="label-col">RUTE/Route</td><td>: {{ $invoice->departure_airport }}</td></tr>
        <tr><td class="label-col">TANGGAL & WAKTU KEDATANGAN/Arrival</td><td>: {{ $invoice->details->where('movement_type', 'Arrival')->first() ? \Carbon\Carbon::parse($invoice->details->where('movement_type', 'Arrival')->first()->actual_time)->format('d M Y, H:i') : '-' }}</td></tr>
        <tr><td class="label-col">TANGGAL & WAKTU KEBERANGKATAN/Departure</td><td>: {{ $invoice->details->where('movement_type', 'Departure')->first() ? \Carbon\Carbon::parse($invoice->details->where('movement_type', 'Departure')->first()->actual_time)->format('d M Y, H:i') : '-' }}</td></tr>
        <tr><td class="label-col">DIBAYAR OLEH/Fee Will Be Paid By</td><td>: {{ $invoice->paid_by ?? $invoice->airline }}</td></tr>
    </table>

    <!-- BAGIAN LOKASI -->
    <table class="charges-table" style="margin-top: 15px; margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="width: 30%; text-align: center;" rowspan="2">KEBERANGKATAN/Departure</th>
                <th style="width: 5%; text-align: center;" rowspan="2">:</th>
                <th style="width: 5%; text-align: center;" rowspan="2">L</th>
                <th style="width: 25%; text-align: center;">Location</th>
                <th style="width: 35%; text-align: center;">Remark</th>
            </tr>
            <tr>
                <th style="text-align: center;">{{ __('1. L') }}</th>
                <th style="text-align: center;">{{ __('1.') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $hasDeparture = $invoice->details->contains('movement_type', 'Departure');
                $hasArrival = $invoice->details->contains('movement_type', 'Arrival');
                $currentAirportCode = $invoice->airport->iata_code ?? 'WAAT';
            @endphp

            @if($hasDeparture)
            <tr>
                <td style="text-align: left; padding: 8px;">KEBERANGKATAN/Departure</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: center;">{{ $currentAirportCode }}</td>
                <td style="text-align: center;">{{ $currentAirportCode }}-{{ $invoice->arrival_airport }}</td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 8px;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;">2</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">-</td>
            </tr>
            @endif

            @if($hasArrival)
            <tr>
                <td style="text-align: left; padding: 8px;">KEDATANGAN/Arrival</td>
                <td style="text-align: center;">:</td>
                <td style="text-align: center;">1</td>
                <td style="text-align: center;">{{ $invoice->departure_airport }}</td>
                <td style="text-align: center;">{{ $invoice->departure_airport }}-{{ $currentAirportCode }}</td>
            </tr>
            <tr>
                <td style="text-align: center; padding: 8px;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;">2</td>
                <td style="text-align: center;">-</td>
                <td style="text-align: center;">-</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- BAGIAN TABEL BIAYA (UPDATED STRUCTURE) -->
    <table class="charges-table">
        <thead>
            <tr>
                <th rowspan="2" class="va-middle" style="width: 25%;">ADVANCED/EXTENDED CHARGES</th>
                <th rowspan="2" class="va-middle" style="width: 10%;">START</th>
                <th rowspan="2" class="va-middle" style="width: 10%;">END</th>
                <th rowspan="2" class="va-middle" style="width: 10%;">DURATION</th>
                <th rowspan="2" class="va-middle" style="width: 15%;">RATE</th>
                <th rowspan="2" class="va-middle" style="width: 15%;">GROSS</th>
                <th rowspan="2" class="va-middle" style="width: 15%;">PPN/VAT 11%</th>
                <th rowspan="2" class="va-middle" style="width: 15%;">NET</th>
            </tr>
            <tr>
                <!-- Empty row for spacing -->
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $detail)
            <tr>
                <td class="text-left" style="padding-left: 8px;">
                    {{ strtoupper($detail->movement_type) }}<br>
                    <small>{{ strtoupper($detail->charge_type) }}</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($detail->charge_type == 'Extend' ? $invoice->operational_hour_end : $detail->actual_time)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($detail->charge_type == 'Extend' ? $detail->actual_time : $invoice->operational_hour_start)->format('H:i') }}</td>
                <td>{{ floor($detail->duration_minutes / 60) }}:{{ str_pad($detail->duration_minutes % 60, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="text-right">
                    @if($invoice->currency == 'USD')
                        ${{ number_format($detail->base_rate, 0) }}
                    @else
                        Rp {{ number_format($detail->base_rate, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'USD')
                        ${{ number_format($detail->base_charge, 0) }}
                    @else
                        Rp {{ number_format($detail->base_charge, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'IDR')
                        Rp {{ number_format($detail->base_charge * 0.11, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'IDR')
                        Rp {{ number_format($detail->base_charge + ($detail->base_charge * 0.11), 0, ',', '.') }}
                    @else
                        ${{ number_format($detail->base_charge, 0) }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- BAGIAN TOTAL SECTION (UPDATED) -->
    <table class="total-section">
        <tr>
            <td class="label-col text-left">PPH PASAL 23</td>
            <td class="text-center currency-col">
                @if($invoice->currency == 'USD')
                    $
                @else
                    Rp
                @endif
            </td>
            <td class="text-right amount-col">
                @if($invoice->currency == 'IDR')
                    - {{ number_format($invoice->pph_charge, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <td class="label-col text-left text-strong">T O T A L</td>
            <td class="text-center currency-col text-strong">
                @if($invoice->currency == 'USD')
                    $
                @else
                    Rp
                @endif
            </td>
            <td class="text-right amount-col text-strong">
                @if($invoice->currency == 'USD')
                    {{ number_format($invoice->total_charge, 0) }}
                @else
                    {{ number_format($invoice->total_charge, 0, ',', '.') }}
                @endif
            </td>
        </tr>
    </table>

    <!-- BAGIAN FOOTER -->
    <div class="note-section">
        Note : Berdasarkan PMK No. 131 Tahun 2024 menggunakan DPP Lain-Lain (11/12 x harga jual / penggantian)x 12%
    </div>

    <div class="bank-details-box">
        <table>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <span class="text-strong">Detail Bank Transfer :</span>
                    <table>
                        <tr><td style="width: 30%;">Nama Bank</td><td>: PT. BANK BRI (PERSERO), TBK</td></tr>
                        <tr><td>Cabang/Branch</td><td>: KCP TUBAN - BALI</td></tr>
                        <tr><td>Nama Rekening</td><td>: LPPNPI BALI</td></tr>
                        <tr><td>Nomor Rekening</td><td>: 2201 01 000 212 306</td></tr>
                    </table>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: middle;">
                    @if($bankLogoSrc)
                        <img src="{{ $bankLogoSrc }}" class="bank-logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-section">
        <div class="signature-block">
            Petugas Official AIRNAV INDONESIA
            <div class="signature-space"></div>
            @if($invoice->creator && $invoice->creator->signature)
                <img src="{{ storage_path('app/public/' . $invoice->creator->signature) }}" style="height: 40px; margin-bottom: 5px;">
            @else
                <div style="height: 45px;"></div>
            @endif
            <div class="text-strong">( {{ $invoice->creator->name ?? '..........................' }} )</div>
        </div>
    </div>
</body>
</html>
