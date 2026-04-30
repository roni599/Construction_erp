@extends('layouts.app')

@section('title', 'Project Details: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>{{ $project->project_name }} <span class="badge badge-{{ $project->status }}">{{ $project->status }}</span></h2>
        <div>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-outline">&larr; Back to Projects</a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-grid">
        <div class="glass-panel">
            <h3>Project Summary</h3>
            <p><strong>Client:</strong> {{ $project->client_name }}</p>
            <p><strong>Manager:</strong> {{ $project->manager->name }}</p>
            <p style="margin-top: 16px;"><strong>Estimated Budget:</strong> 
                @if(is_null($project->estimated_budget))
                    <span style="color: var(--text-secondary);">Not set</span>
                @else
                    Tk. {{ number_format($project->estimated_budget, 2) }}
                @endif
            </p>
        </div>
        
        <div class="glass-panel">
            <h3>Financial Overview</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div>
                    <p><strong>Total P&L:</strong> <br><span style="color: {{ $summary['profit_loss'] >= 0 ? 'var(--success)' : 'var(--danger)' }}; font-size: 18px; font-weight: bold;">Tk. {{ number_format($summary['profit_loss'], 2) }}</span></p>
                    <p style="margin-top: 12px;"><strong>Total Client Payments:</strong> <br>Tk. {{ number_format($summary['total_client_payments'], 2) }}</p>
                </div>
                <div>
                    <p><strong>Manager Balance:</strong> <br><span style="color: {{ $summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}; font-size: 18px; font-weight: bold;">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span></p>
                    <p style="margin-top: 12px; font-size: 13px; color: var(--text-secondary);">
                        Funds: +{{ number_format($summary['total_manager_funds'], 2) }}<br>
                        Expenses: -{{ number_format($summary['total_expenses'], 2) }}<br>
                        Returns: -{{ number_format($summary['total_manager_returns'], 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-top: 32px;">
        <div class="glass-panel" style="text-align: center; padding: 48px 24px; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-file-invoice-dollar" style="font-size: 48px; color: var(--accent-yellow); margin-bottom: 24px; display: block;"></i>
            <h3 style="margin-bottom: 16px;">Project Financial Ledger</h3>
            <p style="margin-bottom: 32px; min-height: 48px;">View the complete, chronological record of all financial transactions for this project.</p>
            <a href="{{ route('admin.projects.ledger', $project->id) }}" class="btn btn-outline" style="width: 100%;">View Ledger &rarr;</a>
        </div>
        
        <div class="glass-panel" style="text-align: center; padding: 48px 24px; transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <i class="fas fa-receipt" style="font-size: 48px; color: var(--accent-blue); margin-bottom: 24px; display: block;"></i>
            <h3 style="margin-bottom: 16px;">Reported Expenses</h3>
            <p style="margin-bottom: 32px; min-height: 48px;">Review all itemized expenses and bills submitted by the project manager.</p>
            <a href="{{ route('admin.projects.expenses', $project->id) }}" class="btn btn-outline" style="width: 100%;">View Expenses &rarr;</a>
        </div>
    </div>
@endsection
