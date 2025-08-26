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
        .title {
            text-align: center;
            line-height: 1.3;
            font-size: 11px;
            padding: 0 10px;
        }
        .header-table .invoice-details-box {
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

        /* Tabel Biaya */
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
        .footer-table { margin-top: 20px; }
        .footer-table .signature-block {
            text-align: center;
            padding-left: 65%;
        }
        .footer-table .signature-space { height: 40px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoSrc = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $bankLogoPath = public_path('images/BRI.png');
        $bankLogoSrc = file_exists($bankLogoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($bankLogoPath)) : '';
        $flightTypeCode = ($invoice->flight_type === 'Domestik') ? '21' : '22';
    @endphp

    <!-- BAGIAN HEADER -->
    <div>
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    @if($logoSrc) <img src="{{ $logoSrc }}" class="logo" style="width: 120px;"> @endif
                </td>
                <td style="width: 50%; text-align: right; vertical-align: bottom;">
                     <div class="invoice-details-box">
                        <table>
                             <tr>
                                 <td><span class="text-strong">NO</span></td>
                                 <td>: {{ $invoice->airport->icao_code ?? '' }}.{{ $flightTypeCode }}.{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y.m') }}&nbsp;&nbsp;&nbsp;&nbsp;{{ str_pad($invoice->invoice_sequence_number, 4, '0', STR_PAD_LEFT) }}</td>
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
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td class="title va-middle">
                    <span class="text-strong">AIRNAV INDONESIA</span><br>
                    PERUM LEMBAGA PENYELENGGARA PELAYANAN NAVIGASI PENERBANGAN INDONESIA<br>
                    <span class="text-strong">ADVANCED/EXTENDED CHARGES</span>
                </td>
            </tr>
        </table>
    </div>

    <div style="border-bottom: 1px solid #000; margin-top: 10px; margin-bottom: 10px;"></div>

    <!-- BAGIAN INFORMASI UTAMA -->
    <table class="main-info-table">
        <colgroup>
            <col style="width: 30%;"> <col style="width: 5%;"> <col style="width: 5%;">
            <col style="width: 27.5%;"> <col style="width: 32.5%;">
        </colgroup>
        <tbody>
            <tr>
                <td>AIRLINE/Airline</td>
                <td colspan="4">: {{ $invoice->airline }}</td>
            </tr>
            <tr>
                <td>GROUND HANDLING</td>
                <td colspan="4">: {{ $invoice->ground_handling ?? $invoice->airline }}</td>
            </tr>
            <tr>
                <td>NOMOR PENERBANGAN/Flight Number</td>
                {{-- --- PERBAIKAN DI SINI --- --}}
                <td colspan="4">
                    : 1. {{ $invoice->flight_number }}
                    @if($invoice->flight_number_2)
                        <br>&nbsp;&nbsp;2. {{ $invoice->flight_number_2 }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>REGISTRASI/Registration</td>
                <td colspan="4">: {{ $invoice->registration }}</td>
            </tr>
            <tr>
                <td>JENIS PESAWAT/Type of Aircraft</td>
                <td colspan="4">: {{ $invoice->aircraft_type }}</td>
            </tr>
            <tr><td colspan="5" style="padding: 5px;"></td></tr>

            @php
                $hasDeparture = $invoice->details->contains('movement_type', 'Departure');
                $hasArrival = $invoice->details->contains('movement_type', 'Arrival');
                $currentAirportCode = $invoice->airport->icao_code ?? 'XXXX';
            @endphp

            <tr>
                <td></td> <td></td> <td></td>
                <td class="text-center text-strong">Location</td>
                <td class="text-center text-strong">Remark</td>
            </tr>

@if($hasArrival && $hasDeparture)
                <tr>
                    <td>KEBERANGKATAN/Departure</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $invoice->departure_airport }}</td>
                    <td class="text-center">1. {{ $invoice->departure_airport }} - {{ $currentAirportCode }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">{{ $currentAirportCode }}</td>
                    <td class="text-center">2. {{ $currentAirportCode }} - {{ $invoice->arrival_airport }}</td>
                </tr>
                <tr>
                    <td>KEDATANGAN/Arrival</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $currentAirportCode }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">{{ $invoice->arrival_airport }}</td>
                    <td></td>
                </tr>
            @elseif($hasArrival)
                {{-- Hanya Arrival: Pesawat datang dari airport asal ke bandara saat ini --}}
                <tr>
                    <td>KEBERANGKATAN/Departure</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $invoice->departure_airport }}</td>
                    <td class="text-center">1. {{ $invoice->departure_airport }} - {{ $currentAirportCode }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">-</td>
                    <td class="text-center">2. -</td>
                </tr>
                <tr>
                    <td>KEDATANGAN/Arrival</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $currentAirportCode }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">-</td>
                    <td></td>
                </tr>
            @elseif($hasDeparture)
                {{-- Hanya Departure: Pesawat berangkat dari bandara saat ini ke tujuan --}}
                <tr>
                    <td>KEBERANGKATAN/Departure</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $currentAirportCode }}</td>
                    <td class="text-center">1. {{ $currentAirportCode }} - {{ $invoice->arrival_airport }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">-</td>
                    <td class="text-center">2. -</td>
                </tr>
                <tr>
                    <td>KEDATANGAN/Arrival</td>
                    <td class="text-center">: 1.</td> <td></td>
                    <td class="text-center">{{ $invoice->arrival_airport }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-center">2.</td> <td></td>
                    <td class="text-center">-</td>
                    <td></td>
                </tr>
            @endif

            <tr><td colspan="5" style="padding: 5px;"></td></tr>
            <tr>
                <td>TANGGAL & WAKTU KEDATANGAN/Arrival</td>
                <td colspan="4">: {{ $invoice->details->where('movement_type', 'Arrival')->first() ? \Carbon\Carbon::parse($invoice->details->where('movement_type', 'Arrival')->first()->actual_time)->format('d M Y, H:i') : '-' }}</td>
            </tr>
            <tr>
                <td>TANGGAL & WAKTU KEBERANGKATAN/Departure</td>
                <td colspan="4">: {{ $invoice->details->where('movement_type', 'Departure')->first() ? \Carbon\Carbon::parse($invoice->details->where('movement_type', 'Departure')->first()->actual_time)->format('d M Y, H:i') : '-' }}</td>
            </tr>
            <tr>
                <td>DIBAYAR OLEH/Fee Will Be Paid By</td>
                <td colspan="4">: {{ $invoice->paid_by }}</td>
            </tr>
        </tbody>
    </table>

    <!-- BAGIAN TABEL BIAYA -->
    <table class="charges-table">
        <thead>
            <tr>
                <th class="va-middle" style="width: 25%;">ADVANCED/EXTENDED CHARGES</th>
                <th class="va-middle" style="width: 10%;">START</th>
                <th class="va-middle" style="width: 10%;">END</th>
                <th class="va-middle" style="width: 10%;">DURATION</th>
                <th class="va-middle" style="width: 15%;">RATE</th>
                <th class="va-middle" style="width: 15%;">GROSS</th>
                <th class="va-middle" style="width: 15%;">PPN/VAT 12%</th>
                <th class="va-middle" style="width: 15%;">NET</th>
            </tr>
            <tr></tr>
        </thead>
        <tbody>
            @php
                $relevantDetail = $invoice->details->firstWhere('base_charge', '>', 0) ?? $invoice->details->first();
            @endphp
            @if($relevantDetail)
            <tr>
                <td class="text-left" style="padding-left: 8px;">
                    <small>{{ strtoupper($relevantDetail->charge_type) }}</small>
                </td>
                <td>{{ \Carbon\Carbon::parse($relevantDetail->charge_type == 'Extend' ? $invoice->operational_hour_end : $relevantDetail->actual_time)->format('H:i') }}</td>
                <td>{{ \Carbon\Carbon::parse($relevantDetail->charge_type == 'Extend' ? $relevantDetail->actual_time : $invoice->operational_hour_start)->format('H:i') }}</td>
                <td>{{ floor($relevantDetail->duration_minutes / 60) }}:{{ str_pad($relevantDetail->duration_minutes % 60, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="text-right">
                    @if($invoice->currency == 'USD')
                        ${{ number_format($relevantDetail->base_rate, 2) }}
                    @else
                        Rp {{ number_format($relevantDetail->base_rate, 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'USD')
                        ${{ number_format($invoice->details->sum('base_charge'), 2) }}
                    @else
                        Rp {{ number_format($invoice->details->sum('base_charge'), 0, ',', '.') }}
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'IDR')
                        Rp {{ number_format($invoice->ppn_charge, 0, ',', '.') }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right">
                    @if($invoice->currency == 'IDR')
                        Rp {{ number_format($invoice->total_charge, 0, ',', '.') }}
                    @else
                        ${{ number_format($invoice->total_charge, 2) }}
                    @endif
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- BAGIAN TOTAL SECTION -->
    <table class="total-section">
        <tr>
            <td class="label-col text-left">PPH PASAL 23</td>
            <td class="text-center currency-col">
                @if($invoice->currency == 'USD') $ @else Rp @endif
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
                @if($invoice->currency == 'USD') $ @else Rp @endif
            </td>
            <td class="text-right amount-col text-strong">
                @if($invoice->currency == 'USD')
                    {{ number_format($invoice->total_charge, 2) }}
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
    <table class="footer-table">
        <tr>
            <td style="width: 30%;" class="cc-block">
                CC:<br> 1. Customer<br> 2. Finance<br> 3. File
            </td>
            <td style="width: 70%;" class="signature-block">
                <div>Petugas Official AIRNAV INDONESIA</div>
                <div>
                @if($invoice->creator && $invoice->creator->signature && file_exists(storage_path('app/public/' . $invoice->creator->signature)))
                    <img src="{{ storage_path('app/public/' . $invoice->creator->signature) }}" style="height: 40px; margin-top: 5px; margin-bottom: 5px;">
                @else
                    <div class="signature-space"></div>
                @endif
                </div>
                <div class="text-strong">( {{ $invoice->creator->name ?? '..........................' }} )</div>
            </td>
        </tr>
    </table>
</body>
</html>
