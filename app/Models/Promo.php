<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    protected $fillable = ['promo_package_id', 'product_id', 'minimum_buy', 'get_free'];

    public function promoPackage()
    {
        return $this->belongsTo(PromoPackage::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
