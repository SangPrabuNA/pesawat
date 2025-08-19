<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'airport_id', // Baru
        'airline',
        'ground_handling', // Baru
        'flight_number',
        'flight_number_2', // Baru
        'registration',
        'aircraft_type',
        'movement_type', // Baru
        'departure_airport', // Kolom Baru
        'arrival_airport',
        'service_type',
        'flight_type',
        'charge_type',
        'actual_time',
        'operational_hour_start',
        'operational_hour_end',
        'duration_minutes',
        'billed_hours',
        'base_rate',
        'base_charge',
        'ppn_charge',
        'pph_charge',
        'apply_pph',
        'is_free_charge',
        'total_charge',
        'currency',
        'status',
        'created_at',
    ];

    /**
     * Mendefinisikan relasi bahwa Invoice milik sebuah Airport.
     */
    public function airport()
    {
        return $this->belongsTo(Airport::class);
    }
    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
