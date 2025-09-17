<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $fillable = [
        'transaction_id',
        'delivery_batch_id',
    ];

    public function deliveryBatch()
    {
        return $this->belongsTo(DeliveryBatch::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
