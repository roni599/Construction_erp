<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ClientPayment;
use App\Models\ManagerFund;
use App\Models\Expense;
use Illuminate\Support\Collection;

class ProjectFinancialService
{
    public function getProjectSummary(Project $project, $startDate = null, $endDate = null, $managerOnly = false): array
    {
        $paymentsQuery = ClientPayment::where('project_id', $project->id);
        $fundsQuery = ManagerFund::where('project_id', $project->id);
        $expensesQuery = Expense::where('project_id', $project->id)->where('status', 'approved');
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

        if ($managerOnly) {
            $paymentsQuery->whereRaw('1 = 0'); // Hide client payments
            $expensesQuery->whereHas('recordedBy', function($q) {
                $q->where('role', 'project_manager');
            });
        }

        $totalClientPayments = $paymentsQuery->sum('amount');
        $totalManagerFunds = $fundsQuery->sum('amount');
        $totalExpenses = $expensesQuery->sum('amount');
        $totalManagerReturns = $returnsQuery->sum('amount');

        // Split expenses by recorder role
        $pmExpenses = Expense::where('project_id', $project->id)
            ->where('status', 'approved')
            ->whereHas('recordedBy', function($q) {
                $q->where('role', 'project_manager');
            })->sum('amount');

        $adminExpenses = Expense::where('project_id', $project->id)
            ->where('status', 'approved')
            ->whereHas('recordedBy', function($q) {
                $q->where('role', 'admin');
            })->sum('amount');

        $managerCashBalance = $totalManagerFunds - $pmExpenses - $totalManagerReturns;
        $adminCashBalance = $totalClientPayments - $adminExpenses - $pmExpenses;
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
            'pm_expenses' => $pmExpenses,
            'admin_expenses' => $adminExpenses,
            'manager_cash_balance' => $managerCashBalance,
            'admin_cash_balance' => $adminCashBalance,
            'profit_loss' => $profitLoss,
            'budget_variance' => $project->estimated_budget - $totalExpenses,
            'start_date' => $project->start_date ? $project->start_date->format('Y-m-d') : '-',
            'end_date' => $project->end_date ? $project->end_date->format('Y-m-d') : '-',
        ];
    }

    public function getProjectLedger(Project $project, $startDate = null, $endDate = null, $invoiceNo = null, $managerOnly = false): Collection
    {
        $paymentsQuery = ClientPayment::where('project_id', $project->id);
        $fundsQuery = ManagerFund::where('project_id', $project->id);
        $expensesQuery = Expense::with(['category', 'recordedBy'])->where('project_id', $project->id);
        $returnsQuery = \App\Models\ManagerReturn::where('project_id', $project->id);

        if ($managerOnly) {
            $expensesQuery->whereHas('recordedBy', function($q) {
                $q->where('role', 'project_manager');
            });
            // Managers shouldn't see client payments in their ledger
            $paymentsQuery->whereRaw('1 = 0'); 
        }

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

        if ($invoiceNo) {
            $paymentsQuery->where(function($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%")->orWhere('id', $invoiceNo);
            });
            $fundsQuery->where(function($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%")->orWhere('id', $invoiceNo);
            });
            $expensesQuery->where(function($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%")->orWhere('id', $invoiceNo);
            });
            $returnsQuery->where(function($q) use ($invoiceNo) {
                $q->where('invoice_no', 'like', "%{$invoiceNo}%")->orWhere('id', $invoiceNo);
            });
        }

        $payments = $paymentsQuery->get()
            ->map(function ($item) use ($managerOnly) {
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
            ->map(function ($item) use ($managerOnly) {
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
            ->map(function ($item) use ($managerOnly) {
                return [
                    'original_id' => $item->id,
                    'id' => $item->invoice_no ?? 'EXP-'.$item->id,
                    'date' => $item->expense_date->format('Y-m-d'),
                    'type' => 'Expense',
                    'description' => ($item->description ? $item->description . ' (' . ($item->category->name ?? 'General') . ')' : 'Payment for ' . ($item->category->name ?? 'Expense')) . (!$managerOnly && $item->recordedBy ? " [Recorded by " . ($item->recordedBy->role === 'admin' ? 'Admin' : 'PM') . ": {$item->recordedBy->name}]" : ""),
                    'credit' => 0,
                    'debit' => $item->status === 'approved' ? $item->amount : 0, // Only approved expenses deduct from balance
                    'amount' => $item->amount,
                    'status' => $item->status,
                ];
            });

        $returns = $returnsQuery->get()
            ->map(function ($item) use ($managerOnly) {
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
        $ledger = collect([])->concat($payments)->concat($funds)->concat($expenses)->concat($returns)->sortByDesc('date')->values();

        return $ledger;
    }
}
