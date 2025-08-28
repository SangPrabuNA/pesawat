<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 11px;
            margin: 15px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .text-strong { font-weight: bold; }
        .va-middle { vertical-align: middle; }
        .page-break { page-break-after: always; }

        /* Header */
        .header-logo { width: 120px; }
        .header-title {
            text-align: center;
            line-height: 1.3;
            font-size: 11px;
            padding: 0 10px;
        }
        .receipt-details-box table td {
            padding: 1px;
            font-size: 10px;
        }

        /* Main Content (STRUKTUR BARU) */
        .main-table { margin-top: 20px; }
        .main-table .label-col {
            width: 35%;
            padding-right: 10px;
        }
        .main-table .value-col {
            width: 65%;
        }
        .main-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .say-box {
            border: 1px solid #000;
            padding: 10px;
            min-height: 40px; /* Menggunakan min-height */
            font-style: italic;
        }

        /* Footer */
        .footer-table { margin-top: 30px; }
        .footer-table td { vertical-align: top; }
        .signature-block {
            text-align: center;
            padding-left: 65%;
        }
        .signature-space { height: 50px; }
        .cc-block { font-size: 9px; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.jpg');
        $logoSrc = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $flightTypeCode = ($invoice->flight_type === 'Domestik') ? '21' : '22';

        // --- FUNGSI TERBILANG DIPERBAIKI ---
        function terbilang($nilai) {
            $nilai = abs($nilai);
            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " ". $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = terbilang($nilai - 10). " Belas";
            } else if ($nilai < 100) {
                $temp = terbilang($nilai/10)." Puluh". terbilang($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " Seratus" . terbilang($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = terbilang($nilai/100) . " Ratus" . terbilang($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " Seribu" . terbilang($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = terbilang($nilai/1000) . " Ribu" . terbilang($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = terbilang($nilai/1000000) . " Juta" . terbilang($nilai % 1000000);
            }
            // Menghapus trim() dari sini agar spasi tidak hilang saat rekursi
            return $temp;
        }

        $exchangeRate = (float) $invoice->usd_exchange_rate;

        $finalAmountInRupiah = 0;
        if ($invoice->currency === 'IDR') {
            $finalAmountInRupiah = $invoice->total_charge;
        } elseif ($invoice->currency === 'USD' && $exchangeRate > 0) {
            $finalAmountInRupiah = $invoice->total_charge * $exchangeRate;
        }

        $finalAmountInRupiah = round($finalAmountInRupiah);

        $totalInWords = 'Nol Rupiah';
        if ($finalAmountInRupiah > 0) {
            // Menambahkan trim() di sini, hanya pada hasil akhir
            $totalInWords = trim(terbilang($finalAmountInRupiah)) . ' Rupiah';
        } elseif ($invoice->currency === 'USD') {
            $totalInWords = 'Kurs Belum Diatur atau Tidak Valid';
        }

        $relevantDetail = $invoice->details->firstWhere('base_charge', '>', 0) ?? $invoice->details->first();
        $paymentType = $relevantDetail ? strtoupper($relevantDetail->charge_type) . ' CHARGES' : 'EXTEND/ADVANCE CHARGES';

    @endphp

    <!-- BAGIAN HEADER KWITANSI -->
    <div>
        <!-- Bagian Atas: Logo dan Nomor Kwitansi -->
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: left;">
                    @if($logoSrc) <img src="{{ $logoSrc }}" class="header-logo"> @endif
                </td>
                <td style="width: 50%; text-align: right; vertical-align: bottom;">
                     <div class="receipt-details-box">
                        <table>
                             <tr>
                                 <td><span class="text-strong">Receipt Number</span></td>
                                 <td>: {{ $invoice->airport->icao_code ?? '' }}.{{ $flightTypeCode }}.{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y.m') }}&nbsp;&nbsp;&nbsp;&nbsp;{{ str_pad($invoice->invoice_sequence_number, 4, '0', STR_PAD_LEFT) }}</td>
                             </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bagian Bawah: Judul -->
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td class="header-title va-middle">
                    <span class="text-strong">AIRNAV INDONESIA</span><br>
                    PERUM LEMBAGA PENYELENGGARA PELAYANAN NAVIGASI PENERBANGAN INDONESIA<br>
                    <span class="text-strong">ADVANCED/EXTENDED CHARGES</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- GARIS PEMBATAS -->
    <div style="border-bottom: 1px solid #000; margin-top: 10px; margin-bottom: 10px;"></div>

    <!-- BAGIAN ISI KWITANSI (STRUKTUR BARU) -->
    <table class="main-table">
        <tr>
            <td class="label-col">TANGGAL/Date</td>
            <td class="value-col">: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label-col">TERIMA DARI/Received From</td>
            <td class="value-col">: {{ $invoice->airline }}</td>
        </tr>
        <tr>
            <td class="label-col">SEJUMLAH/Amount</td>
            <td class="value-col">: {{ $invoice->currency }} {{ number_format($invoice->total_charge, $invoice->currency === 'IDR' ? 0 : 2, ',', '.') }}</td>
        </tr>

        @if($invoice->currency == 'USD' && $exchangeRate > 0)
        <tr>
            <td class="label-col">KURS/Exchange Rate</td>
            <td class="value-col">: Rp {{ number_format($exchangeRate, 2, ',', '.') }}</td>
        </tr>
        @endif

        <tr>
            <td class="label-col">TERBILANG/Say</td>
            <td class="value-col">
                <div class="say-box">
                    : {{ ucwords($totalInWords) }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="label-col">PEMBAYARAN/Payment</td>
            <td class="value-col">: {{ $paymentType }}</td>
        </tr>
        <tr>
            <td class="label-col text-strong">JUMLAH YANG DIBAYARKAN/Amount To Paid</td>
            <td class="value-col text-strong">: Rp {{ number_format($finalAmountInRupiah, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- BAGIAN FOOTER KWITANSI -->
    <table class="footer-table">
        <tr>
            <td style="width: 30%;" class="cc-block">
                CC:<br>
                1. Customer<br>
                2. Finance<br>
                3. File
            </td>
            <td style="width: 70%;" class="signature-block">
                <div>Manager Administrasi dan Keuangan</div>
                <div style="height: 80px; margin: 15px 0; text-align: center;">
                    @if(isset($signatureData) && $signatureData)
                        <img src="{{ $signatureData }}" style="max-height: 100px; max-width: 300px; height: auto; width: auto;">
                    @elseif($invoice->signatory && $invoice->signatory->signature)
                        @php
                            $signaturePath = storage_path('app/public/' . $invoice->signatory->signature);
                            $signatureSrc = '';
                            if (file_exists($signaturePath)) {
                                $imageData = file_get_contents($signaturePath);
                                $extension = pathinfo($invoice->signatory->signature, PATHINFO_EXTENSION);
                                $mimeType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
                                $signatureSrc = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                            }
                        @endphp
                        @if($signatureSrc)
                            <img src="{{ $signatureSrc }}" style="max-height: 100px; max-width: 300px; height: auto; width: auto;">
                        @else
                            <div style="height: 70px;"></div>
                        @endif
                    @else
                        <div style="height: 70px;"></div>
                    @endif
                </div>
                <div class="text-strong">( {{ $invoice->signatory->name ?? '..........................' }} )</div>
            </td>
        </tr>
    </table>
</body>
</html>
