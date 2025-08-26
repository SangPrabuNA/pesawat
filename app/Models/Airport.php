<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    // Hapus $guarded = [] dan gunakan $fillable saja untuk menghindari konflik
    protected $fillable = [
        'name',
        'iata_code',
        'icao_code',
        'op_start',
        'op_end',
        'is_active',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
