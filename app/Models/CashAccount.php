<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'account_number',
        'bank_name',
        'initial_balance',
        'current_balance',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function recalculateBalance(): void
    {
        $totalExpenses = $this->expenses()
            ->where('status', 'approved')
            ->sum('amount');

        $this->update([
            'current_balance' => $this->initial_balance - $totalExpenses,
        ]);
    }
}
