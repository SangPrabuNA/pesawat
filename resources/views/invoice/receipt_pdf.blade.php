<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kwitansi #{{ $invoice->id }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 11px;
            margin: 20px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td { vertical-align: middle; }
        .header-table .logo { width: 120px; }
        .header-table .title {
            text-align: center;
            line-height: 1.3;
            font-size: 12px;
            font-weight: bold;
        }
        .header-table .receipt-info { text-align: right; }
        .main-table { margin-top: 20px; }
        .main-table td { padding: 5px 0; }
        .main-table .label-col { width: 35%; }
        .main-table .say-box {
            border: 1px solid #000;
            padding: 10px;
            height: 40px;
            vertical-align: top;
            font-style: italic;
        }
        .footer-table { margin-top: 30px; }
        .footer-table td { vertical-align: top; }
        .footer-table .signature-block {
            text-align: center;
            padding-left: 60%;
        }
        .footer-table .signature-space { height: 50px; }
        .footer-table .cc-block { font-size: 9px; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        // Helper untuk mengubah angka menjadi terbilang
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
            return trim($temp);
        }
        $logoPath = public_path('images/logo.jpg');
        $logoSrc = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        // --- PERBAIKAN LOGIKA KONVERSI RUPIAH ---
        $totalInRupiah = 0;
        $totalInWords = '';
        $exchangeRate = (float) $invoice->usd_exchange_rate; // Ubah ke tipe float untuk memastikan

        // Tentukan jumlah total dalam Rupiah terlebih dahulu
        if ($invoice->currency === 'IDR') {
            $totalInRupiah = $invoice->total_charge;
        } elseif ($invoice->currency === 'USD' && $exchangeRate > 0) {
            $totalInRupiah = $invoice->total_charge * $exchangeRate;
        }

        // Tentukan teks "terbilang" berdasarkan hasil konversi Rupiah
        if ($totalInRupiah > 0) {
            $totalInWords = terbilang($totalInRupiah) . ' Rupiah';
        } elseif ($invoice->currency === 'USD') {
            $totalInWords = 'Kurs Belum Diatur atau Tidak Valid';
        } else {
            $totalInWords = 'Nol Rupiah';
        }
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 20%;">@if($logoSrc) <img src="{{ $logoSrc }}" class="logo"> @endif</td>
            <td style="width: 50%;" class="title">
                AIRNAV INDONESIA<br>
                PERUM LEMBAGA PENYELENGGARA PELAYANAN NAVIGASI PENERBANGAN INDONESIA<br>
                ADVANCED/EXTENDED CHARGES
            </td>
            <td style="width: 30%;" class="receipt-info">
                <span class="bold">Receipt Number :</span> WATK.21.2023.09 {{ str_pad($invoice->id, 4, '0', STR_PAD_LEFT) }}
            </td>
        </tr>
    </table>

    <table class="main-table">
        <tr>
            <td class="label-col">TANGGAL/Date</td>
            <td>: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="label-col">TERIMA DARI/Received From</td>
            <td>: {{ $invoice->airline }}</td>
        </tr>
        <tr>
            <td class="label-col">SEJUMLAH/Amount</td>
            <td>: {{ $invoice->currency }} {{ number_format($invoice->total_charge, 2, ',', '.') }}</td>
        </tr>
        @if($invoice->currency == 'USD')
        <tr>
            <td class="label-col">KURS/Exchange Rate</td>
            <td>: Rp {{ number_format($exchangeRate, 2, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="label-col">TERBILANG/Say</td>
            <td class="say-box">: {{ ucwords($totalInWords) }}</td>
        </tr>
        <tr>
            <td class="label-col">PEMBAYARAN/Payment</td>
            <td>: EXTEND/ADVANCE CHARGES</td>
        </tr>
        <tr>
            <td class="label-col bold">JUMLAH YANG DIBAYARKAN/Amount To Paid</td>
            {{-- Selalu tampilkan jumlah akhir dalam Rupiah --}}
            <td class="bold">: Rp {{ number_format($totalInRupiah, 2, ',', '.') }}</td>
        </tr>
    </table>

    <table class="footer-table">
        <tr>
            <td style="width: 60%;" class="cc-block">
                CC:<br>
                1. Customer<br>
                2. Finance<br>
                3. File
            </td>
            <td style="width: 40%;" class="signature-block">
                Petugas Official AIRNAV INDONESIA
                <div class="signature-space"></div>
                <div class="bold">( .......................... )</div>
            </td>
        </tr>
    </table>

</body>
</html>
