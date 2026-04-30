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

    <div class="glass-panel">
        <h3>My Projects</h3>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('manager.projects.show', $project->id) }}" style="color: inherit; text-decoration: none;">
                                    <strong>{{ $project->project_name }}</strong>
                                </a>
                            </td>
                            <td>{{ $project->client_name }}</td>
                            <td>{{ $project->location ?? '-' }}</td>
                            <td><span class="badge badge-{{ $project->status }}">{{ $project->status }}</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('manager.projects.ledger', $project->id) }}">
                                            <i class="fas fa-file-invoice"></i> Ledger
                                        </a>
                                        <a class="dropdown-item" href="{{ route('manager.projects.show', $project->id) }}">
                                            <i class="fas fa-plus-circle"></i> Record Expense
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">No projects assigned to you.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
