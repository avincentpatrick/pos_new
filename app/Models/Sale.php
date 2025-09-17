<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'sale_status_id',
        'transaction_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function stockMovement()
    {
        return $this->hasOne(StockMovement::class, 'sales_id');
    }
}
