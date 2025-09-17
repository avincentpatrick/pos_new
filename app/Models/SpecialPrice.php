<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialPrice extends Model
{
    protected $fillable = ['special_price_set_id', 'product_id', 'special_price'];

    public function specialPriceSet()
    {
        return $this->belongsTo(SpecialPriceSet::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
