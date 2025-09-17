<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StorageDutyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'time_in',
        'time_out',
        'created_by',
        'updated_by',
        'storage_duty_log_status_id',
    ];

    protected $casts = [
        'time_in' => 'datetime',
        'time_out' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(StorageDutyLogStatusType::class, 'storage_duty_log_status_id');
    }

    public function startStockCount(): HasOne
    {
        return $this->hasOne(StockCount::class)->where('count_type_id', 1); // 1 for Start Shift Count
    }

    public function endStockCount(): HasOne
    {
        return $this->hasOne(StockCount::class)->where('count_type_id', 2); // 2 for End Shift Count
    }
}
