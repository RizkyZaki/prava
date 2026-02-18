<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ticket_prefix',
        'color',
        'start_date',
        'end_date',
        'pinned_date',
        'status',
        'completed_at',
        'company_id',
        'region_id',
        'institution_id',
        'sub_institution',
        'project_value',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pinned_date' => 'datetime',
        'completed_at' => 'datetime',
        'project_value' => 'decimal:2',
    ];

    public function getIsPinnedAttribute(): bool
    {
        return !is_null($this->pinned_date);
    }

    public function pin(): void
    {
        $this->update(['pinned_date' => now()]);
    }

    public function unpin(): void
    {
        $this->update(['pinned_date' => null]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function disbursements(): HasMany
    {
        return $this->hasMany(Disbursement::class);
    }

    public function getTotalExpensesAttribute(): float
    {
        return $this->expenses()->where('status', 'approved')->sum('amount');
    }

    public function getTotalDisbursementsAttribute(): float
    {
        return $this->disbursements()->sum('amount');
    }

    public function ticketStatuses(): HasMany
    {
        return $this->hasMany(TicketStatus::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withTimestamps();
    }

    public function epics(): HasMany
    {
        return $this->hasMany(Epic::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class);
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->end_date) {
            return null;
        }

        $today = Carbon::today();
        $endDate = Carbon::parse($this->end_date);

        if ($today->gt($endDate)) {
            return 0;
        }

        return $today->diffInDays($endDate);
    }

    public function getProgressPercentageAttribute(): float
    {
        $totalTickets = $this->tickets()->count();

        if ($totalTickets === 0) {
            return 0.0;
        }

        $completedTickets = $this->tickets()
            ->whereHas('status', function ($query) {
                $query->where('is_completed', true);
            })
            ->count();

        return round(($completedTickets / $totalTickets) * 100, 1);
    }

    public function externalAccess(): HasOne
    {
        return $this->hasOne(ExternalAccess::class);
    }

    public function generateExternalAccess()
    {
        $this->externalAccess()?->delete();

        return ExternalAccess::generateForProject($this->id);
    }
}
