<?php

namespace App\Exports;

use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PerAirportSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    private $airport;
    private $year;
    private $month;
    private $rowNumber = 0;

    public function __construct($airport, $year, $month)
    {
        $this->airport = $airport;
        $this->year = $year;
        $this->month = $month;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = Invoice::query()
            ->with(['airport', 'creator', 'details'])
            ->where('airport_id', $this->airport->id)
            ->orderByRaw('YEAR(created_at) asc')
            ->orderBy('invoice_sequence_number', 'asc');

        if ($this->year) {
            $query->whereYear('created_at', $this->year);
        }
        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        return $query;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->airport->iata_code;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No.', 'No Invoice', 'Tanggal Invoice', 'Airline', 'Call Sign', 'Registrasi A/C',
            'DOM/INT', 'Movement', 'ATD/ATA', 'Extend/Advance', 'Durasi (Jam:Menit)',
            'Tagihan', 'PPN', 'PPH', 'Total Tagihan', 'Status Pembayaran',
        ];
    }

    /**
     * @param mixed $invoice
     * @return array
     */
    public function map($invoice): array
    {
        $flightTypeCode = ($invoice->flight_type === 'Domestik') ? '21' : '22';
        $invoiceDate = Carbon::parse($invoice->created_at);
        $formattedInvoiceNumber = sprintf(
            '%s.%s.%s.%s.%s',
            $invoice->airport->icao_code ?? 'ICAO', $flightTypeCode,
            $invoiceDate->format('Y'), $invoiceDate->format('m'),
            str_pad($invoice->invoice_sequence_number, 4, '0', STR_PAD_LEFT)
        );

        $callSign = $invoice->flight_number;
        if (!empty($invoice->flight_number_2)) {
            $callSign .= ' & ' . $invoice->flight_number_2;
        }

        $movements = $invoice->details->pluck('movement_type')->implode(' & ');
        $totalDurationMinutes = $invoice->details->sum('duration_minutes');
        $hours = floor($totalDurationMinutes / 60);
        $minutes = $totalDurationMinutes % 60;
        $formattedDuration = sprintf('%02d:%02d', $hours, $minutes);

        $actualTime = '';
        $chargeTypeDisplay = '';
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
            if (!$detailToUseForTime) $detailToUseForTime = $invoice->details->first();
            if ($detailToUseForTime) $actualTime = Carbon::parse($detailToUseForTime->actual_time)->format('H:i');
        }

        $exchangeRate = $invoice->usd_exchange_rate ?? 1;
        $isInternational = $invoice->flight_type === 'Internasional';
        $baseCharge = $invoice->details->sum('base_charge');
        $ppnCharge = $invoice->ppn_charge;
        $pphCharge = $invoice->pph_charge;
        $totalCharge = $invoice->total_charge;

        if ($isInternational && $exchangeRate > 0) {
            $baseCharge *= $exchangeRate;
            $ppnCharge *= $exchangeRate;
            $pphCharge *= $exchangeRate;
            $totalCharge *= $exchangeRate;
        }

        $formatRupiah = fn($amount) => 'Rp. ' . number_format($amount, 0, ',', '.');

        return [
            ++$this->rowNumber,
            $formattedInvoiceNumber, $invoice->created_at->isoFormat('D-MMMM-YYYY'),
            $invoice->airline, $callSign, $invoice->registration, $invoice->flight_type,
            $movements, $actualTime, $chargeTypeDisplay, $formattedDuration,
            $formatRupiah($baseCharge), $formatRupiah($ppnCharge), $formatRupiah($pphCharge),
            $formatRupiah($totalCharge), ucfirst($invoice->status),
        ];
    }

    /**
     * Mengatur lebar kolom.
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No.
            'B' => 28,  // No Invoice
            'C' => 20,  // Tanggal Invoice
            'D' => 22,  // Airline
            'E' => 20,  // Call Sign
            'F' => 15,  // Registrasi A/C
            'G' => 12,  // DOM/INT
            'H' => 20,  // Movement
            'I' => 10,  // ATD/ATA
            'J' => 15,  // Extend/Advance
            'K' => 18,  // Durasi (Jam:Menit)
            'L' => 20,  // Tagihan
            'M' => 20,  // PPN
            'N' => 20,  // PPH
            'O' => 20,  // Total Tagihan
            'P' => 18,  // Status Pembayaran
        ];
    }

    /**
     * Memberikan style pada sheet.
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Membuat baris header menjadi tebal (bold) dan rata tengah
        return [
            1 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }

    /**
     * Mendaftarkan event setelah sheet dibuat.
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Mengambil range sel dari A1 hingga sel terakhir yang berisi data
                $cellRange = 'A1:' . $event->sheet->getHighestColumn() . $event->sheet->getHighestRow();

                // Menerapkan garis batas (border) tipis ke seluruh sel dalam range
                $event->sheet->getDelegate()->getStyle($cellRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
