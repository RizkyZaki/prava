<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'google_id',
        'fingerprint_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withTimestamps();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assignedTickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_users');
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function isAssignedToTicket(Ticket $ticket): bool
    {
        return $this->assignedTickets()->where('ticket_id', $ticket->id)->exists();
    }

    public function assignToTicket(Ticket $ticket): void
    {
        $this->assignedTickets()->syncWithoutDetaching($ticket->id);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    public function unreadNotifications(): HasMany
    {
        return $this->hasMany(Notification::class)->unread()->orderBy('created_at', 'desc');
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class)->orderBy('attendance_date', 'desc');
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)
            ->whereDate('attendance_date', today())
            ->latest();
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function activeSalary()
    {
        return $this->hasOne(Salary::class)
            ->where('is_active', true)
            ->whereDate('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', now());
            });
    }

    public function salaryDeductions(): HasMany
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    public function monthlyPayrolls(): HasMany
    {
        return $this->hasMany(MonthlyPayroll::class);
    }

    public function permittedAbsences(): HasMany
    {
        return $this->hasMany(PermittedAbsence::class);
    }

    public function createdEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Check if user is active
        if (!$this->is_active) {
            return false;
        }

        return true;
    }
}
