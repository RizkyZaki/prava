<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('widget.overview');
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if ($isSuperAdmin) {
            return $this->getSuperAdminStats();
        } else {
            return $this->getUserStats();
        }
    }

    protected function getSuperAdminStats(): array
    {
        $totalProjects = Project::count();
        $totalTickets = Ticket::count();
        $usersCount = User::count();
        $myTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->where('ticket_users.user_id', auth()->id())
            ->count();

        return [
            Stat::make(__('widget.stat.total_projects'), $totalProjects)
                ->description(__('widget.stat.desc.total_projects'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make(__('widget.stat.total_tickets'), $totalTickets)
                ->description(__('widget.stat.desc.total_tickets'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make(__('widget.stat.my_assigned_tickets'), $myTickets)
                ->description(__('widget.stat.desc.my_assigned_tickets'))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('info'),

            Stat::make(__('widget.stat.team_members'), $usersCount)
                ->description(__('widget.stat.desc.team_members_admin'))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }

    protected function getUserStats(): array
    {
        $user = auth()->user();

        $myProjects = $user->projects()->count();

        $myProjectIds = $user->projects()->pluck('projects.id')->toArray();

        $projectTickets = Ticket::whereIn('project_id', $myProjectIds)->count();

        $myAssignedTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->where('ticket_users.user_id', $user->id)
            ->count();

        $myCreatedTickets = Ticket::where('created_by', $user->id)->count();

        $newTicketsThisWeek = Ticket::whereIn('project_id', $myProjectIds)
            ->where('tickets.created_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $myOverdueTickets = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $user->id)
            ->where('tickets.due_date', '<', Carbon::now())
            ->whereNotIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->count();

        $myCompletedThisWeek = DB::table('tickets')
            ->join('ticket_users', 'tickets.id', '=', 'ticket_users.ticket_id')
            ->join('ticket_statuses', 'tickets.ticket_status_id', '=', 'ticket_statuses.id')
            ->where('ticket_users.user_id', $user->id)
            ->whereIn('ticket_statuses.name', ['Completed', 'Done', 'Closed'])
            ->where('tickets.updated_at', '>=', Carbon::now()->subDays(7))
            ->count();

        $teamMembers = User::whereHas('projects', function ($query) use ($myProjectIds) {
            $query->whereIn('projects.id', $myProjectIds);
        })->where('id', '!=', $user->id)->count();

        return [
            Stat::make(__('widget.stat.my_projects'), $myProjects)
                ->description(__('widget.stat.desc.my_projects'))
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make(__('widget.stat.my_assigned_tickets'), $myAssignedTickets)
                ->description(__('widget.stat.desc.my_assigned_tickets'))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($myAssignedTickets > 10 ? 'danger' : ($myAssignedTickets > 5 ? 'warning' : 'success')),

            Stat::make(__('widget.stat.my_created_tickets'), $myCreatedTickets)
                ->description(__('widget.stat.desc.my_created_tickets'))
                ->descriptionIcon('heroicon-m-pencil-square')
                ->color('info'),

            Stat::make(__('widget.stat.project_tickets'), $projectTickets)
                ->description(__('widget.stat.desc.project_tickets'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('success'),

            Stat::make(__('widget.stat.completed_this_week'), $myCompletedThisWeek)
                ->description(__('widget.stat.desc.completed_this_week'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($myCompletedThisWeek > 0 ? 'success' : 'gray'),

            Stat::make(__('widget.stat.new_tasks_this_week'), $newTicketsThisWeek)
                ->description(__('widget.stat.desc.new_tasks_this_week'))
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make(__('widget.stat.my_overdue_tasks'), $myOverdueTickets)
                ->description(__('widget.stat.desc.my_overdue_tasks'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($myOverdueTickets > 0 ? 'danger' : 'success'),

            Stat::make(__('widget.stat.team_members'), $teamMembers)
                ->description(__('widget.stat.desc.team_members_user'))
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
