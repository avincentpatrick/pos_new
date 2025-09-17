<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierDutyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_duty_log_status_id',
        'user_id',
        'time_in',
        'time_out',
        'created_by',
        'updated_by',
    ];

    public function cashCount()
    {
        return $this->hasOne(CashCount::class)->where('count_type_id', 1);
    }

    public function endCashCount()
    {
        return $this->hasOne(CashCount::class)->where('count_type_id', 2);
    }
}
