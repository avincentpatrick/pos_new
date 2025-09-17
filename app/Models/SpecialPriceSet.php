<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialPriceSet extends Model
{
    protected $fillable = ['special_price_set_name', 'validity_date'];

    public function specialPrices()
    {
        return $this->hasMany(SpecialPrice::class);
    }

    public function clientSpecialPrices()
    {
        return $this->hasMany(ClientSpecialPrice::class);
    }
}
