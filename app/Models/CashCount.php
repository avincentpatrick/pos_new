<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_duty_log_id',
        'count_type_id',
        'total_amount',
    ];

    public function cashierDutyLog()
    {
        return $this->belongsTo(CashierDutyLog::class);
    }

    public function cashCountItems()
    {
        return $this->hasMany(CashCountItem::class);
    }
}
