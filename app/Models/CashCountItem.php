<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCountItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_count_id',
        'denomination_id',
        'quantity',
    ];

    public function cashCount()
    {
        return $this->belongsTo(CashCount::class);
    }

    public function denomination()
    {
        return $this->belongsTo(Denomination::class);
    }
}
