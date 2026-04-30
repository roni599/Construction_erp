<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ClientPayment;
use App\Models\ManagerFund;
use App\Models\Expense;
use Illuminate\Support\Collection;

class ProjectFinancialService
{
    public function getProjectSummary(Project $project, $startDate = null, $endDate = null): array
    {
        $paymentsQuery = ClientPayment::where('project_id', $project->id);
        $fundsQuery = ManagerFund::where('project_id', $project->id);
        $expensesQuery = Expense::where('project_id', $project->id);
        $returnsQuery = \App\Models\ManagerReturn::where('project_id', $project->id);

        if ($startDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $startDate);
            $fundsQuery->whereDate('fund_date', '>=', $startDate);
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
            $returnsQuery->whereDate('return_date', '>=', $startDate);
        }

        if ($endDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $endDate);
            $fundsQuery->whereDate('fund_date', '<=', $endDate);
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
            $returnsQuery->whereDate('return_date', '<=', $endDate);
        }

        $totalClientPayments = $paymentsQuery->sum('amount');
        $totalManagerFunds = $fundsQuery->sum('amount');
        $totalExpenses = $expensesQuery->sum('amount');
        $totalManagerReturns = $returnsQuery->sum('amount');

        $managerCashBalance = $totalManagerFunds - $totalExpenses - $totalManagerReturns;
        $profitLoss = $totalClientPayments - $totalExpenses;

        return [
            'project_id' => $project->id,
            'project_name' => $project->project_name,
            'manager_name' => $project->manager->name ?? 'N/A',
            'estimated_budget' => $project->estimated_budget,
            'total_client_payments' => $totalClientPayments,
            'total_manager_funds' => $totalManagerFunds,
            'total_manager_returns' => $totalManagerReturns,
            'total_expenses' => $totalExpenses,
            'manager_cash_balance' => $managerCashBalance,
            'profit_loss' => $profitLoss,
            'budget_variance' => $project->estimated_budget - $totalExpenses,
            'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : '-',
            'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : '-',
        ];
    }

    public function getProjectLedger(Project $project, $startDate = null, $endDate = null): Collection
    {
        $paymentsQuery = ClientPayment::where('project_id', $project->id);
        $fundsQuery = ManagerFund::where('project_id', $project->id);
        $expensesQuery = Expense::with('category')->where('project_id', $project->id);
        $returnsQuery = \App\Models\ManagerReturn::where('project_id', $project->id);

        if ($startDate) {
            $paymentsQuery->whereDate('payment_date', '>=', $startDate);
            $fundsQuery->whereDate('fund_date', '>=', $startDate);
            $expensesQuery->whereDate('expense_date', '>=', $startDate);
            $returnsQuery->whereDate('return_date', '>=', $startDate);
        }

        if ($endDate) {
            $paymentsQuery->whereDate('payment_date', '<=', $endDate);
            $fundsQuery->whereDate('fund_date', '<=', $endDate);
            $expensesQuery->whereDate('expense_date', '<=', $endDate);
            $returnsQuery->whereDate('return_date', '<=', $endDate);
        }

        $payments = $paymentsQuery->get()
            ->map(function ($item) {
                return [
                    'original_id' => $item->id,
                    'id' => $item->invoice_no ?? 'PAY-'.$item->id,
                    'date' => $item->payment_date->format('Y-m-d'),
                    'type' => 'Client Payment',
                    'description' => 'Received via ' . $item->payment_method . ($item->reference_no ? ' (Ref: '.$item->reference_no.')' : ''),
                    'credit' => $item->amount,
                    'debit' => 0,
                ];
            });

        $funds = $fundsQuery->get()
            ->map(function ($item) {
                return [
                    'original_id' => $item->id,
                    'id' => $item->invoice_no ?? 'FND-'.$item->id,
                    'date' => $item->fund_date->format('Y-m-d'),
                    'type' => 'Fund Disbursed',
                    'description' => 'Disbursed to Manager via ' . $item->payment_method,
                    'credit' => 0,
                    'debit' => $item->amount, // Money leaving company bank
                ];
            });

        $expenses = $expensesQuery->get()
            ->map(function ($item) {
                return [
                    'original_id' => $item->id,
                    'id' => $item->invoice_no ?? 'EXP-'.$item->id,
                    'date' => $item->expense_date->format('Y-m-d'),
                    'type' => 'Expense (' . $item->category->name . ')',
                    'description' => $item->description ?? 'Project Expense',
                    'credit' => 0,
                    'debit' => $item->amount, // Money leaving project funds
                ];
            });

        $returns = $returnsQuery->get()
            ->map(function ($item) {
                return [
                    'original_id' => $item->id,
                    'id' => $item->invoice_no ?? 'RET-'.$item->id,
                    'date' => $item->return_date->format('Y-m-d'),
                    'type' => 'Fund Returned by PM',
                    'description' => 'Remaining cash returned to admin via ' . $item->payment_method,
                    'credit' => $item->amount, // Money returning to company bank
                    'debit' => 0,
                ];
            });

        // Combine all transactions and sort by date ascending
        $ledger = collect([])->concat($payments)->concat($funds)->concat($expenses)->concat($returns)->sortBy('date')->values();

        // Calculate running balances
        $runningBalance = 0;
        $ledger->transform(function ($item) use (&$runningBalance) {
            // Note: Ledger running balance can be relative to the company.
            // Client payment = money in (+). Fund = money out (-). Expense = money out (-).
            // But wait, if we gave fund, money is still in project.
            // Let's do pure P&L ledger: Client Payments (Credit/In) - Expenses (Debit/Out).
            // Funds are internal transfers, so we might want to separate the "Manager Cash Ledger" vs "Project P&L Ledger".
            
            // We will just provide the raw entries for now.
            return $item;
        });

        return $ledger;
    }
}
