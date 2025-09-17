<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientPromo extends Model
{
    protected $fillable = ['client_id', 'promo_package_id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function promoPackage()
    {
        return $this->belongsTo(PromoPackage::class);
    }
}
