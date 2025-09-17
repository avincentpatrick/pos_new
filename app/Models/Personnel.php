<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $fillable = [
        'personnel_name',
        'personnel_type_id',
    ];

    public function personnelType()
    {
        return $this->belongsTo(PersonnelType::class);
    }
}
