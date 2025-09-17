<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_status_id',
        'name',
        'company',
        'contact_no',
        'email',
        'address',
        'google_map_pin',
        'created_by',
        'updated_by',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function clientPromo()
    {
        return $this->hasOne(ClientPromo::class);
    }

    public function clientSpecialPrice()
    {
        return $this->hasOne(ClientSpecialPrice::class);
    }
}
