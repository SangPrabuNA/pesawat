<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Mendefinisikan relasi bahwa Airport memiliki banyak Invoice.
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
