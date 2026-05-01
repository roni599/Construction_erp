@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="flex-between" style="margin-bottom: 16px;">
        <h2 style="margin: 0;">Dashboard Overview</h2>
    </div>

    <div class="dashboard-grid" style="gap: 16px; margin-bottom: 24px;">
        <div class="glass-panel stat-card" style="padding: 16px;" data-stat-type="total-projects">
            <span class="stat-title" style="font-size: 12px;">Total Projects</span>
            <span class="stat-value" style="font-size: 24px;">{{ $totalProjects }}</span>
        </div>
        <div class="glass-panel stat-card" style="padding: 16px;" data-stat-type="active-projects">
            <span class="stat-title" style="font-size: 12px;">Active Projects</span>
            <span class="stat-value" style="font-size: 24px;">{{ $activeProjects }}</span>
        </div>
        <div class="glass-panel stat-card" style="padding: 16px;" data-stat-type="profit-loss">
            <span class="stat-title" style="font-size: 12px;">Overall Profit / Loss</span>
            <span class="stat-value" style="font-size: 24px; color: {{ $totalProfitLoss >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ number_format($totalProfitLoss, 0) }}
            </span>
            <small class="stat-desc" style="color: var(--text-secondary); font-size: 0.6rem;">P&L = Payment - (Fund - Expense)</small>
        </div>
        <div class="glass-panel stat-card" style="padding: 16px;" data-stat-type="hand-cash">
            <span class="stat-title" style="font-size: 12px;">
                @if($isSearch)
                    PM hand cash
                @else
                    All PM Total Hand Cash
                @endif
            </span>
            <span class="stat-value" style="font-size: 24px; color: {{ $totalPmHandCash >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                {{ number_format($totalPmHandCash, 0) }}
            </span>
            <small class="stat-desc" style="color: var(--text-secondary); font-size: 0.6rem;">
                @if($selectedManager)
                    {{ $selectedManager->name }}
                @else
                    Held by all PMs
                @endif
            </small>
        </div>
    </div>

    <div class="glass-panel" style="margin-bottom: 24px; padding: 20px;">
        <form method="GET" action="{{ route('admin.dashboard') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 12px;">Search Project</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8); font-size: 13px;">
                    <option value="">All Projects</option>
                    @foreach($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->project_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label" style="font-size: 12px;">Project Manager</label>
                <select name="employee_id" class="form-control" style="background: rgba(0,0,0,0.8); font-size: 13px;">
                    <option value="">All Managers</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 12px;">Start Date</label>
                <input type="date" name="start_date" class="form-control" style="font-size: 13px;" value="{{ request('start_date') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 12px;">End Date</label>
                <input type="date" name="end_date" class="form-control" style="font-size: 13px;" value="{{ request('end_date') }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px;"><i class="fas fa-filter"></i> Filter</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline" style="padding: 10px 20px;">Clear</a>
            </div>
        </form>
    </div>

    <h3>Project Summaries</h3>
    <div class="table-wrapper glass-panel" style="padding: 0; overflow: hidden;">
        <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: rgba(255, 255, 255, 0.05);">
                    <th style="padding: 12px 8px; text-align: left;">Project Name</th>
                    <th style="padding: 12px 8px; text-align: left;">PM</th>
                    <th style="padding: 12px 8px; text-align: right;">Budget</th>
                    <th style="padding: 12px 8px; text-align: right;">Payments</th>
                    <th style="padding: 12px 8px; text-align: right;">PM Recv.</th>
                    <th style="padding: 12px 8px; text-align: right;">PM Ref.</th>
                    <th style="padding: 12px 8px; text-align: right;">Expenses</th>
                    <th style="padding: 12px 8px; text-align: right;">PM Bal.</th>
                    <th style="padding: 12px 8px; text-align: right;">P&L</th>
                    <th style="padding: 12px 8px; text-align: center;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($summaries as $summary)
                    <tr>
                        <td style="padding: 10px 8px;">
                            <a href="{{ route('admin.projects.show', $summary['project_id']) }}" style="color: inherit; text-decoration: none;">
                                <strong>{{ $summary['project_name'] }}</strong>
                            </a>
                        </td>
                        <td style="padding: 10px 8px;">{{ $summary['manager_name'] }}</td>
                        <td style="text-align: right; padding: 10px 8px;">{{ number_format($summary['estimated_budget'], 0) }}</td>
                        <td style="text-align: right; padding: 10px 8px;">{{ number_format($summary['total_client_payments'], 0) }}</td>
                        <td style="text-align: right; padding: 10px 8px;">{{ number_format($summary['total_manager_funds'], 0) }}</td>
                        <td style="text-align: right; padding: 10px 8px; color: var(--accent-yellow);">{{ number_format($summary['total_manager_returns'], 0) }}</td>
                        <td style="text-align: right; padding: 10px 8px;">{{ number_format($summary['total_expenses'], 0) }}</td>
                        <td style="text-align: right; padding: 10px 8px; color: {{ $summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ number_format($summary['manager_cash_balance'], 0) }}
                        </td>
                        <td style="text-align: right; padding: 10px 8px; color: {{ $summary['profit_loss'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ number_format($summary['profit_loss'], 0) }}
                        </td>
                        <td style="text-align: center; padding: 10px 8px;">
                            <a href="{{ route('admin.projects.show', $summary['project_id']) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 30px;">No projects found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($summaries) > 0)
                <tfoot style="border-top: 1px solid var(--border-color); background: rgba(255, 255, 255, 0.05); font-weight: bold;">
                    <tr>
                        <td colspan="2" style="text-align: right; padding: 12px 8px;">SEARCH TOTAL:</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ number_format($searchTotals['budget'], 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ number_format($searchTotals['payments'], 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ number_format($searchTotals['funds'], 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ number_format($searchTotals['returns'], 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ number_format($searchTotals['expenses'], 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px; color: {{ $searchTotals['balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ number_format($searchTotals['balance'], 0) }}
                        </td>
                        <td style="text-align: right; padding: 12px 8px; color: {{ $searchTotals['profit_loss'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                            {{ number_format($searchTotals['profit_loss'], 0) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
