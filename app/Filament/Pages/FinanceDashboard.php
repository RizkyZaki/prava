<?php

namespace App\Filament\Pages;

use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class FinanceDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Dashboard Keuangan';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.finance-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole(['super_admin', 'finance']);
    }

    public function getViewData(): array
    {
        $companies = Company::where('is_active', true)->get();
        $selectedCompanyId = request()->get('company_id');

        // Stats Cards
        $totalCashBalance = CashAccount::where('is_active', true)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('current_balance');

        $totalExpensesThisMonth = Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        $totalExpensesLastMonth = Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->subMonth()->month)
            ->whereYear('expense_date', now()->subMonth()->year)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        $totalIncomesThisMonth = Income::where('status', 'approved')
            ->whereMonth('income_date', now()->month)
            ->whereYear('income_date', now()->year)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        $totalIncomesLastMonth = Income::where('status', 'approved')
            ->whereMonth('income_date', now()->subMonth()->month)
            ->whereYear('income_date', now()->subMonth()->year)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        $pendingExpenses = Expense::where('status', 'pending')
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->count();

        $pendingAmount = Expense::where('status', 'pending')
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        $pendingIncomes = Income::where('status', 'pending')
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->count();

        $pendingIncomeAmount = Income::where('status', 'pending')
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->sum('amount');

        // Monthly Expense Chart (12 bulan terakhir)
        $monthlyExpenses = $this->getMonthlyExpenses($selectedCompanyId);

        // Monthly Income Chart (12 bulan terakhir)
        $monthlyIncomes = $this->getMonthlyIncomes($selectedCompanyId);

        // Expense by Category (Pie chart)
        $categoryExpenses = $this->getCategoryExpenses($selectedCompanyId);

        // Expense by Company
        $companyExpenses = $this->getCompanyExpenses();

        // Cash Account Balances
        $cashAccounts = CashAccount::with('company')
            ->where('is_active', true)
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->get();

        // Recent expenses
        $recentExpenses = Expense::with(['company', 'category', 'creator', 'cashAccount'])
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Recent incomes
        $recentIncomes = Income::with(['company', 'creator', 'cashAccount', 'project'])
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId))
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Expense change percentage
        $expenseChange = $totalExpensesLastMonth > 0
            ? round((($totalExpensesThisMonth - $totalExpensesLastMonth) / $totalExpensesLastMonth) * 100, 1)
            : 0;

        // Income change percentage
        $incomeChange = $totalIncomesLastMonth > 0
            ? round((($totalIncomesThisMonth - $totalIncomesLastMonth) / $totalIncomesLastMonth) * 100, 1)
            : 0;

        return [
            'companies' => $companies,
            'selectedCompanyId' => $selectedCompanyId,
            'totalCashBalance' => $totalCashBalance,
            'totalExpensesThisMonth' => $totalExpensesThisMonth,
            'totalExpensesLastMonth' => $totalExpensesLastMonth,
            'totalIncomesThisMonth' => $totalIncomesThisMonth,
            'totalIncomesLastMonth' => $totalIncomesLastMonth,
            'pendingExpenses' => $pendingExpenses,
            'pendingAmount' => $pendingAmount,
            'pendingIncomes' => $pendingIncomes,
            'pendingIncomeAmount' => $pendingIncomeAmount,
            'monthlyExpenses' => $monthlyExpenses,
            'monthlyIncomes' => $monthlyIncomes,
            'categoryExpenses' => $categoryExpenses,
            'companyExpenses' => $companyExpenses,
            'cashAccounts' => $cashAccounts,
            'recentExpenses' => $recentExpenses,
            'recentIncomes' => $recentIncomes,
            'expenseChange' => $expenseChange,
            'incomeChange' => $incomeChange,
        ];
    }

    protected function getMonthlyExpenses(?string $companyId): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Expense::where('status', 'approved')
                ->whereMonth('expense_date', $date->month)
                ->whereYear('expense_date', $date->year)
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->sum('amount');

            $data[] = [
                'month' => $date->translatedFormat('M Y'),
                'amount' => (float) $amount,
            ];
        }
        return $data;
    }

    protected function getCategoryExpenses(?string $companyId): array
    {
        return Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->select('expense_categories.name', 'expense_categories.color', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('expense_categories.name', 'expense_categories.color')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    protected function getCompanyExpenses(): array
    {
        return Expense::where('status', 'approved')
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->join('companies', 'expenses.company_id', '=', 'companies.id')
            ->select('companies.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('companies.name')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    protected function getMonthlyIncomes(?string $companyId): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Income::where('status', 'approved')
                ->whereMonth('income_date', $date->month)
                ->whereYear('income_date', $date->year)
                ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
                ->sum('amount');

            $data[] = [
                'month' => $date->translatedFormat('M Y'),
                'amount' => (float) $amount,
            ];
        }
        return $data;
    }
}
