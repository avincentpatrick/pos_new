<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'cashier_duty_log_id',
        'client_id',
        'transaction_id',
        'payment_method_id',
        'amount_received',
        'amount_change',
        'reference_number',
        'check_number',
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cashierDutyLog()
    {
        return $this->belongsTo(CashierDutyLog::class);
    }
}
