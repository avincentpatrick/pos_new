<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockCount extends Model
{
    protected $fillable = [
        'storage_duty_log_id',
        'count_type_id',
    ];

    public function storageDutyLog(): BelongsTo
    {
        return $this->belongsTo(StorageDutyLog::class);
    }

    public function stockCountItems(): HasMany
    {
        return $this->hasMany(StockCountItems::class);
    }
}
