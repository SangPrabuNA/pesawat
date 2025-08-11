<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'movement_type', 'actual_time', 'charge_type',
        'duration_minutes', 'billed_hours', 'base_rate', 'base_charge',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}