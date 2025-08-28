<?php

namespace App\Exports;

use App\Models\Airport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\Auth;

class InvoicesExport implements WithMultipleSheets
{
    use Exportable;

    protected $year;
    protected $month;
    protected $airportId;

    public function __construct($year = null, $month = null, $airportId = null)
    {
        $this->year = $year;
        $this->month = $month;
        $this->airportId = $airportId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $user = Auth::user();

        // Kondisi 1: Jika pengguna adalah master dan TIDAK memfilter bandara,
        // buat satu sheet untuk setiap bandara.
        if ($user->role === 'master' && empty($this->airportId)) {
            $airports = Airport::where('is_active', true)->orderBy('iata_code')->get();
            foreach ($airports as $airport) {
                $sheets[] = new PerAirportSheet($airport, $this->year, $this->month);
            }
        }
        // Kondisi 2: Jika pengguna memfilter bandara (atau bukan master),
        // buat hanya satu sheet untuk bandara yang dipilih/dimiliki.
        else {
            $airportIdToLoad = ($user->role === 'master') ? $this->airportId : $user->airport_id;
            $airport = Airport::find($airportIdToLoad);
            if ($airport) {
                $sheets[] = new PerAirportSheet($airport, $this->year, $this->month);
            }
        }

        return $sheets;
    }
}
