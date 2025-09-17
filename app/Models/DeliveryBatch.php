<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryBatch extends Model
{
    protected $fillable = [
        'driver_id',
        'helper_id',
        'route_id',
        'finalize_id', // Add finalize_id
        'delivery_batch_status_id',
    ];

    public function driver()
    {
        return $this->belongsTo(Personnel::class, 'driver_id');
    }

    public function helper()
    {
        return $this->belongsTo(Personnel::class, 'helper_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }
}
