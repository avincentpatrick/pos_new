<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSpecialPrice extends Model
{
    protected $fillable = ['client_id', 'special_price_set_id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function specialPriceSet()
    {
        return $this->belongsTo(SpecialPriceSet::class);
    }
}
