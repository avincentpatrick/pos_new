<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_status_id',
        'client_id',
        'order_type_id',
        'total_amount', // Keep total_amount
        'note',
        'created_by',
        'updated_by',
        'cashier_duty_log_id',
    ];

    public function cashierDutyLog()
    {
        return $this->belongsTo(CashierDutyLog::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payments() // Change to plural for hasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function orderType()
    {
        return $this->belongsTo(OrderType::class);
    }

    // Accessor for dynamic remaining_balance
    public function getRemainingBalanceAttribute()
    {
        return $this->adjusted_total - $this->payments->sum('amount_received');
    }

    public function getAdjustedTotalAttribute()
    {
        $paymentMethodIds = $this->payments->pluck('payment_method_id');

        if ($paymentMethodIds->contains(3) || $paymentMethodIds->contains(7)) {
            $adjustedTotal = 0;

            foreach ($this->sales as $sale) {
                $stockMovement = $sale->stockMovement;

                if ($stockMovement && in_array($stockMovement->dispense_status_type_id, [1, 3])) {
                    $adjustedTotal += ($stockMovement->actual_quantity_dispensed ?? 0) * $sale->price;
                } else {
                    $adjustedTotal += $sale->total;
                }
            }

            return $adjustedTotal;
        }

        return $this->total_amount;
    }

    public function getDispenseStatusAttribute()
    {
        $salesCount = $this->sales->count();
        $dispensedCount = $this->sales()->whereHas('stockMovement')->count();

        if ($salesCount == 0) {
            return 'N/A';
        }

        if ($dispensedCount == 0) {
            return 'Pending';
        }

        if ($dispensedCount < $salesCount) {
            return 'Partially Dispensed';
        }

        return 'Fully Dispensed';
    }
}
