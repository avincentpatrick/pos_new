<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseType extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_type_name',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
