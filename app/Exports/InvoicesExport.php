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
    protected $airportId; // Tambahkan properti baru
    private $rowNumber = 0;

    // Update constructor untuk menerima airportId
    public function __construct($year, $month, $airportId)
    {
        $this->year = $year;
        $this->month = $month;
        $this->airportId = $airportId; // Simpan airportId
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    public function query()
    {
        return Invoice::query()
            ->when($this->year, function ($query, $year) {
                return $query->whereRaw("strftime('%Y', actual_time) = ?", [$year]);
            })
            ->when($this->month, function ($query, $month) {
                return $query->whereRaw("strftime('%m', actual_time) = ?", [str_pad($month, 2, '0', STR_PAD_LEFT)]);
            })
            ->when($this->airportId, function ($query, $airportId) { // Tambahkan kondisi filter bandara
                return $query->where('airport_id', $airportId);
            })
            ->orderBy('actual_time', 'asc');
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Menambahkan kolom Bandara di awal
        return [
            'NO',
            'Bandara', // Kolom baru
            'Tanggal',
            'Nama Airline',
            'Call Sign',
            'Registrasi A/C',
            'DOM/INT',
            'ATD/ATA',
            'Extend/Advance',
            'Durasi (Jam:Menit)',
            'Tagihan',
            'PPN 11%',
            'PPH 23',
            'Total Tagihan',
            'Status Pembayaran',
        ];
    }

    /**
     * @param Invoice $invoice
     * @return array
     */
    public function map($invoice): array
    {
        $totalMinutes = $invoice->duration_minutes;
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $formattedDuration = $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

        return [
            ++$this->rowNumber,
            $invoice->airport->iata_code ?? 'N/A', // Tambahkan data bandara
            Carbon::parse($invoice->actual_time)->format('d-m-Y'),
            $invoice->airline,
            $invoice->flight_number,
            $invoice->registration,
            $invoice->flight_type,
            Carbon::parse($invoice->actual_time)->format('H:i'),
            $invoice->charge_type,
            $formattedDuration,
            $invoice->base_charge,
            $invoice->ppn_charge,
            $invoice->pph_charge,
            $invoice->total_charge,
            $invoice->status,
        ];
    }
}
