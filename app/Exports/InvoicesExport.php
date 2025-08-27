<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $year;
    protected $month;
    protected $airport_id;

    /**
     * InvoicesExport constructor.
     *
     * @param int|null $year
     * @param int|null $month
     * @param int|null $airport_id
     */
    public function __construct($year = null, $month = null, $airport_id = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->airport_id = $airport_id;
        // Set locale Carbon ke Indonesia untuk format tanggal
        Carbon::setLocale('id');
    }

    /**
    * @return \Illuminate\Database\Eloquent\Builder
    */
    public function query()
    {
        // Memulai query dengan eager loading dan mengurutkan berdasarkan nomor sequence
        $query = Invoice::query()->with(['airport', 'creator', 'details'])->orderBy('invoice_sequence_number', 'asc');

        // Terapkan filter airport_id jika ada
        if ($this->airport_id) {
            $query->where('airport_id', $this->airport_id);
        }

        // Terapkan filter tahun jika ada
        if ($this->year) {
            $query->whereYear('created_at', $this->year);
        }

        // Terapkan filter bulan jika ada
        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Mendefinisikan header baru untuk file Excel
        return [
            'No Invoice',
            'Tanggal Invoice',
            'Call Sign',
            'Registrasi A/C',
            'DOM/INT',
            'Movement',
            'ATD/ATA',
            'Extend/Advance',
            'Durasi (Jam:Menit)',
            'Tagihan',
            'PPN 12%',
            'PPH 23',
            'Total Tagihan',
            'Status Pembayaran',
        ];
    }

    /**
     * @param mixed $invoice
     * @return array
     */
    public function map($invoice): array
    {
        // Gabungkan call signs jika ada dua
        $callSign = $invoice->flight_number;
        if (!empty($invoice->flight_number_2)) {
            $callSign .= ' & ' . $invoice->flight_number_2;
        }

        // Ambil data dasar dari relasi 'details'
        $movements = $invoice->details->pluck('movement_type')->implode(' & ');

        // Logika format durasi
        $totalDurationMinutes = $invoice->details->sum('duration_minutes');
        $hours = floor($totalDurationMinutes / 60);
        $minutes = $totalDurationMinutes % 60;
        $formattedDuration = sprintf('%02d:%02d', $hours, $minutes);


        // Logika terpadu untuk ATD/ATA dan EXTEND/ADVANCE
        $actualTime = '';
        $chargeTypeDisplay = ''; // Variabel untuk kolom EXTEND/ADVANCE

        if ($invoice->details->isNotEmpty()) {
            $detailToUseForTime = null;
            $hasAdvance = $invoice->details->contains('charge_type', 'Advance');
            $hasExtend = $invoice->details->contains('charge_type', 'Extend');

            if ($hasAdvance) {
                $chargeTypeDisplay = 'Advance';
                $detailToUseForTime = $invoice->details->firstWhere('movement_type', 'Arrival');
            } elseif ($hasExtend) {
                $chargeTypeDisplay = 'Extend';
                $detailToUseForTime = $invoice->details->firstWhere('movement_type', 'Departure');
            }

            if (!$detailToUseForTime) {
                $detailToUseForTime = $invoice->details->first();
            }

            if ($detailToUseForTime) {
                $actualTime = Carbon::parse($detailToUseForTime->actual_time)->format('H:i');
            }
        }

        // --- PERUBAHAN DI SINI: Logika konversi dan format mata uang ---
        $exchangeRate = $invoice->usd_exchange_rate ?? 1;
        $isInternational = $invoice->flight_type === 'Internasional';

        // Ambil nilai dasar
        $baseCharge = $invoice->details->sum('base_charge');
        $ppnCharge = $invoice->ppn_charge;
        $pphCharge = $invoice->pph_charge;
        $totalCharge = $invoice->total_charge;

        // Jika internasional, konversi nilai ke Rupiah
        if ($isInternational && $exchangeRate > 0) {
            $baseCharge *= $exchangeRate;
            $ppnCharge *= $exchangeRate;
            $pphCharge *= $exchangeRate;
            $totalCharge *= $exchangeRate;
        }

        // Fungsi helper untuk memformat angka menjadi format Rupiah tanpa desimal
        $formatRupiah = function ($amount) {
            return 'Rp. ' . number_format($amount, 0, ',', '.');
        };

        return [
            $invoice->invoice_sequence_number,
            $invoice->created_at->isoFormat('D MMMM YYYY'),
            $callSign,
            $invoice->registration,
            $invoice->flight_type,
            $movements,
            $actualTime,
            $chargeTypeDisplay,
            $formattedDuration,
            $formatRupiah($baseCharge),
            $formatRupiah($ppnCharge),
            $formatRupiah($pphCharge),
            $formatRupiah($totalCharge),
            ucfirst($invoice->status),
        ];
    }
}
