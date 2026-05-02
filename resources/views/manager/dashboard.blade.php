@extends('layouts.app')

@section('title', 'PM Dashboard')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>My Dashboard</h2>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 32px;">
        <div class="glass-panel stat-card">
            <span class="stat-title">Assigned Projects</span>
            <span class="stat-value">{{ $assignedProjectsCount }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Received</span>
            <span class="stat-value" style="color: var(--accent-blue);">Tk. {{ number_format($totalReceived, 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Expenses</span>
            <span class="stat-value" style="color: var(--danger);">Tk. {{ number_format($totalExpenses, 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Returned</span>
            <span class="stat-value" style="color: var(--accent-yellow);">Tk. {{ number_format($totalReturns ?? 0, 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Current Balance</span>
            <span class="stat-value" style="color: {{ $balance >= 0 ? 'var(--success)' : 'var(--danger)' }}">Tk. {{ number_format($balance, 2) }}</span>
        </div>
    </div>

    <style>
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .search-input-group {
                flex: 1 1 auto !important;
                max-width: 100% !important;
            }
            .search-button-group {
                width: 100%;
                justify-content: space-between;
            }
            .search-button-group .btn {
                flex: 1;
            }
        }
    </style>

    <!-- Search Section -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('manager.dashboard') }}" class="search-form" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
            <div class="form-group search-input-group" style="margin: 0; flex: 0 0 300px;">
                <label class="form-label">Search Project</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">-- All Projects --</option>
                    @foreach($allMyProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="search-button-group" style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('manager.dashboard') }}" class="btn btn-outline" style="padding: 8px 16px;">Clear</a>
            </div>
        </form>
    </div>

    <div class="glass-panel">
        <h3>My Project Financial Overview</h3>
        <div class="table-wrapper" style="margin-top: 16px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Assigned Project</th>
                        <th style="text-align: right;">Total Received</th>
                        <th style="text-align: right;">Total Expense</th>
                        <th style="text-align: right;">Fund Return</th>
                        <th style="text-align: right;">Current Balance</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('manager.projects.show', $project->id) }}" style="color: inherit; text-decoration: none;">
                                    <div style="font-weight: 600; color: var(--accent-blue);">{{ $project->project_name }}</div>
                                    <div style="font-size: 11px; color: var(--text-secondary);">{{ $project->client_name }}</div>
                                </a>
                            </td>
                            <td style="text-align: right; color: var(--accent-blue);">Tk. {{ number_format($project->summary['total_manager_funds'], 2) }}</td>
                            <td style="text-align: right; color: var(--danger);">Tk. {{ number_format($project->summary['total_expenses'], 2) }}</td>
                            <td style="text-align: right; color: var(--accent-yellow);">Tk. {{ number_format($project->summary['total_manager_returns'], 2) }}</td>
                            <td style="text-align: right; font-weight: 600; color: {{ $project->summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                Tk. {{ number_format($project->summary['manager_cash_balance'], 2) }}
                            </td>
                            <td>
                                <div class="dropdown" style="display: flex; justify-content: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('manager.projects.ledger', $project->id) }}">
                                            <i class="fas fa-file-invoice"></i> Ledger
                                        </a>
                                        <a class="dropdown-item" href="{{ route('manager.expenses.create', ['project_id' => $project->id]) }}">
                                            <i class="fas fa-plus-circle"></i> Record Expense
                                        </a>
                                        <a class="dropdown-item" href="{{ route('manager.projects.show', $project->id) }}">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">No projects found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
