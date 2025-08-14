<?php

namespace App\Exports;

use App\Models\InvoiceDetail; // <-- Change model to InvoiceDetail
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $year;
    protected $month;
    protected $airportId;
    private $rowNumber = 0;

    public function __construct($year, $month, $airportId)
    {
        $this->year = $year;
        $this->month = $month;
        $this->airportId = $airportId;
    }

    /**
     * The query is now based on InvoiceDetail.
     */
    public function query()
    {
        return InvoiceDetail::query()
            ->with(['invoice.airport']) // Eager load relationships
            ->whereHas('invoice', function ($query) { // Apply filters to the parent invoice
                $query->when($this->year, function ($q, $year) {
                    return $q->whereYear('created_at', $year);
                })
                ->when($this->month, function ($q, $month) {
                    return $q->whereMonth('created_at', $month);
                })
                ->when($this->airportId, function ($q, $airportId) {
                    return $q->where('airport_id', $airportId);
                });
            })
            ->orderBy('actual_time', 'asc'); // Now we can correctly order by actual_time
    }

    /**
     * The headings are updated to reflect the detailed view.
     */
    public function headings(): array
    {
        return [
            'NO',
            'Invoice ID',
            'Bandara',
            'Tanggal Aktual',
            'Waktu Aktual',
            'Airline',
            'Call Sign',
            'Registrasi A/C',
            'Movement',
            'Jenis Biaya',
            'Durasi (Jam:Menit)',
            'Total Tagihan (Invoice)',
            'Status Pembayaran',
        ];
    }

    /**
     * The map function now processes an InvoiceDetail object.
     * @param InvoiceDetail $detail
     */
    public function map($detail): array
    {
        $invoice = $detail->invoice; // Get the parent invoice from the relationship

        $totalMinutes = $detail->duration_minutes;
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $formattedDuration = $hours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);

        return [
            ++$this->rowNumber,
            $invoice->id,
            $invoice->airport->iata_code ?? 'N/A',
            Carbon::parse($detail->actual_time)->format('d-m-Y'),
            Carbon::parse($detail->actual_time)->format('H:i'),
            $invoice->airline,
            $invoice->flight_number,
            $invoice->registration,
            $detail->movement_type,
            $detail->charge_type,
            $formattedDuration,
            number_format($invoice->total_charge, 2) . ' ' . $invoice->currency,
            $invoice->status,
        ];
    }
}
