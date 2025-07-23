<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'airline',
        'flight_number',
        'registration',
        'aircraft_type',
        'route',
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
        'total_charge',
        'currency',
    ];
}
