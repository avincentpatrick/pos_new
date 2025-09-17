<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'storage_duty_log_id',
        'dispense_status_type_id',
        'return_reason_id',
        'return_reason_specify',
        'actual_quantity_dispensed',
        'actual_quantity_returned',
        'sales_id',
        'product_id',
        'quantity',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function returnReason()
    {
        return $this->belongsTo(ReturnReason::class, 'return_reason_id');
    }

    public function dispenseStatusType()
    {
        return $this->belongsTo(DispenseStatusType::class, 'dispense_status_type_id');
    }

    // Self-referencing relationship for returns
    public function parentStockMovement()
    {
        return $this->belongsTo(StockMovement::class, 'stock_movement_id');
    }

    public function storageDutyLog()
    {
        return $this->belongsTo(StorageDutyLog::class);
    }
}
