<?php

namespace App\Providers;

use App\Filament\Resources\TicketResource\Pages\EditCommentModal;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Pages\BasePage as Page;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(\App\Models\Salary::class, \App\Policies\SalaryPolicy::class);
        Gate::policy(\App\Models\MonthlyPayroll::class, \App\Policies\MonthlyPayrollPolicy::class);
        Gate::policy(\App\Models\SalaryDeduction::class, \App\Policies\SalaryDeductionPolicy::class);
        Gate::policy(\App\Models\Company::class, \App\Policies\CompanyPolicy::class);
        Gate::policy(\App\Models\ExpenseCategory::class, \App\Policies\ExpenseCategoryPolicy::class);
        Gate::policy(\App\Models\CashAccount::class, \App\Policies\CashAccountPolicy::class);
        Gate::policy(\App\Models\Expense::class, \App\Policies\ExpensePolicy::class);
        Gate::policy(\App\Models\Income::class, \App\Policies\IncomePolicy::class);

        Livewire::component('edit-comment-modal', EditCommentModal::class);
        FilamentShield::buildPermissionKeyUsing(
            function (string $entity, string $affix, string $subject, string $case, string $separator) {
                return match(true) {
                    # if `configurePermissionIdentifierUsing()` was used previously, then this needs to be adjusted accordingly
                    is_subclass_of($entity, Resource::class) => Str::of($affix)
                        ->snake()
                        ->append('_')
                        ->append(
                            Str::of($entity)
                                ->afterLast('\\')
                                ->beforeLast('Resource')
                                ->replace('\\', '')
                                ->snake()
                                ->replace('_', '::')
                        )
                        ->toString(),
                    is_subclass_of($entity, Page::class) => Str::of('page_')
                        ->append(class_basename($entity))
                        ->toString(),
                    is_subclass_of($entity, Widget::class) => Str::of('widget_')
                        ->append(class_basename($entity))
                        ->toString()
                    };
            });
    }
}
