@extends('layouts.app')

@section('title', 'Financials: ' . $employee->name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Financial Overview: {{ $employee->name }}</h2>
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline">&larr; Back to Project Managers</a>
    </div>

    <div class="dashboard-grid">
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Funds Received</span>
            <span class="stat-value" style="color: var(--accent-blue);">Tk. {{ number_format($totalFunds, 2) }}</span>
        </div>
        
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Expenses Logged</span>
            <span class="stat-value" style="color: var(--danger);">Tk. {{ number_format($totalExpenses, 2) }}</span>
        </div>

        <div class="glass-panel stat-card">
            <span class="stat-title">Total Returns to Admin</span>
            <span class="stat-value" style="color: var(--accent-blue);">Tk. {{ number_format($totalReturns, 2) }}</span>
        </div>

        <div class="glass-panel stat-card">
            <span class="stat-title">Current Cash Balance</span>
            <span class="stat-value" style="color: {{ $balance >= 0 ? 'var(--success)' : 'var(--danger)' }}">Tk. {{ number_format($balance, 2) }}</span>
        </div>
    </div>
    
    <div class="glass-panel" style="margin-top: 32px;">
        <h3>Project-wise Balance Breakdown</h3>
        <p style="font-size: 14px; margin-bottom: 16px;">This table shows the individual ledger balance for each project assigned to {{ $employee->name }}.</p>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th style="text-align: right;">Funds Received</th>
                        <th style="text-align: right;">Expenses</th>
                        <th style="text-align: right;">Returned</th>
                        <th style="text-align: right;">Project Balance</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($projectSummary as $summary)
                        <tr>
                            <td><strong>{{ $summary['project_name'] }}</strong></td>
                            <td style="text-align: right; color: var(--accent-blue);">Tk. {{ number_format($summary['received'], 2) }}</td>
                            <td style="text-align: right; color: var(--danger);">Tk. {{ number_format($summary['expenses'], 2) }}</td>
                            <td style="text-align: right; color: var(--accent-blue);">Tk. {{ number_format($summary['returns'], 2) }}</td>
                            <td style="text-align: right; font-weight: bold; color: {{ $summary['balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                Tk. {{ number_format($summary['balance'], 2) }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.projects.show', $summary['id']) }}" class="btn btn-outline" style="padding: 4px 12px; font-size: 11px;">View Project Ledger</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top: 32px; grid-template-columns: 1fr 1fr;">
        <!-- Funds Received -->
        <div class="glass-panel" style="grid-column: span 1;">
            <h3>Funds Received</h3>
            @if($employee->managerFunds->count() > 0)
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Project</th>
                                <th>Method</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->managerFunds->sortByDesc('fund_date') as $fund)
                                <tr>
                                    <td>{{ $fund->fund_date->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('admin.projects.show', $fund->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                            {{ $fund->project->project_name }}
                                        </a>
                                    </td>
                                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fund->payment_method) }}</td>
                                    <td style="color: var(--success);">+Tk. {{ number_format($fund->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="color: var(--text-secondary); margin-top: 16px;">No funds have been transferred to this project manager yet.</p>
            @endif
        </div>

        <!-- Expenses Logged -->
        <div class="glass-panel" style="grid-column: span 1;">
            <h3>Expenses Logged</h3>
            @if($employee->expenses->count() > 0)
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Project</th>
                                <th>Category</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employee->expenses->sortByDesc('expense_date') as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('admin.projects.show', $expense->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                            {{ $expense->project->project_name }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->category->name }}</td>
                                    <td style="color: var(--danger);">-Tk. {{ number_format($expense->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="color: var(--text-secondary); margin-top: 16px;">This project manager has not logged any expenses yet.</p>
            @endif
        </div>
    </div>
@endsection
