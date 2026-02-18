<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'cash_account_id',
        'project_id',
        'created_by',
        'approved_by',
        'title',
        'description',
        'amount',
        'income_date',
        'source',
        'receipt',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'income_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::created(function (Income $income) {
            if ($income->status === 'approved') {
                $income->cashAccount->recalculateBalance();
            }
        });

        static::updated(function (Income $income) {
            if ($income->wasChanged('status') || $income->wasChanged('amount')) {
                $income->cashAccount->recalculateBalance();
            }
        });

        static::deleted(function (Income $income) {
            $income->cashAccount->recalculateBalance();
        });
    }
}
