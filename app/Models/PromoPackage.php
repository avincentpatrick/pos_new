<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoPackage extends Model
{
    protected $fillable = ['promo_package_name', 'validity_date'];

    public function promos()
    {
        return $this->hasMany(Promo::class);
    }

    public function clientPromos()
    {
        return $this->hasMany(ClientPromo::class);
    }
}
