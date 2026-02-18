<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'cash_account_id',
        'expense_category_id',
        'project_id',
        'created_by',
        'approved_by',
        'title',
        'description',
        'amount',
        'expense_date',
        'receipt',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function cashAccount(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
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
        static::created(function (Expense $expense) {
            if ($expense->status === 'approved') {
                $expense->cashAccount->recalculateBalance();
            }
        });

        static::updated(function (Expense $expense) {
            if ($expense->wasChanged('status') || $expense->wasChanged('amount')) {
                $expense->cashAccount->recalculateBalance();
            }
        });

        static::deleted(function (Expense $expense) {
            $expense->cashAccount->recalculateBalance();
        });
    }
}
