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

    <div class="glass-panel" style="margin-top: 32px;">
        <div class="flex-between" style="margin-bottom: 20px;">
            <h3>Unified Transaction Ledger</h3>
            <div style="font-size: 13px; color: var(--text-secondary);">Chronological record of all manager transactions</div>
        </div>
        
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Project</th>
                        <th>Description</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right; white-space: nowrap;">Credit (In)</th>
                        <th style="text-align: right; white-space: nowrap;">Debit (Out)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $item)
                        <tr>
                            <td>
                                <a href="{{ $item->invoice_url }}" style="color: var(--accent-blue); font-weight: 600;">
                                    {{ $item->invoice_no ?? 'REF-'.str_pad($item->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td>{{ $item->date->format('d M, Y') }}</td>
                            <td>
                                @if($item->direction === 'in')
                                    <span class="badge" style="background: rgba(0, 230, 118, 0.1); color: var(--success);">Credit</span>
                                @else
                                    <span class="badge" style="background: rgba(255, 76, 76, 0.1); color: var(--danger);">Debit</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $item->project_id) }}" style="color: var(--text-primary); text-decoration: none;">
                                    {{ $item->project }}
                                </a>
                            </td>
                            <td style="font-size: 13px; color: var(--text-secondary);">
                                <span style="color: var(--text-primary); font-weight: 500;">[{{ $item->type }}]</span> {{ $item->description }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="
                                    padding: 4px 10px; font-size: 11px; font-weight: 600;
                                    @if($item->status === 'approved')
                                        background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);
                                    @elseif($item->status === 'rejected')
                                        background: rgba(220, 53, 69, 0.1); color: var(--danger); border: 1px solid var(--danger);
                                    @else
                                        background: rgba(255, 193, 7, 0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);
                                    @endif
                                ">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: var(--success); white-space: nowrap;">
                                @if($item->direction === 'in')
                                    Tk.{{ number_format($item->amount, 2) }}
                                @else
                                    <span style="color: var(--text-secondary); opacity: 0.3;">-</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 600; color: var(--danger); white-space: nowrap; {{ $item->status === 'rejected' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                @if($item->direction === 'out')
                                    Tk.{{ number_format($item->amount, 2) }}
                                @else
                                    <span style="color: var(--text-secondary); opacity: 0.3;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px; color: var(--text-secondary);">
                                No financial transactions found for this project manager.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: rgba(255,255,255,0.03); font-weight: bold; border-top: 2px solid var(--border-color);">
                        <td colspan="6" style="text-align: right; padding: 12px 16px;">Total Summary:</td>
                        <td style="text-align: right; color: var(--success); padding: 12px 16px; border-bottom: 3px double var(--border-color);">
                            Tk. {{ number_format($totalFunds, 2) }}
                        </td>
                        <td style="text-align: right; color: var(--danger); padding: 12px 16px; border-bottom: 3px double var(--border-color);">
                            Tk. {{ number_format($totalExpenses + $totalReturns, 2) }}
                        </td>
                    </tr>
                    <tr style="background: rgba(255,255,255,0.05); font-weight: 800;">
                        <td colspan="6" style="text-align: right; padding: 12px 16px;">Current Cash in Hand:</td>
                        <td colspan="2" style="text-align: center; color: {{ $balance >= 0 ? 'var(--success)' : 'var(--danger)' }}; padding: 12px 16px; font-size: 16px;">
                            Tk. {{ number_format($balance, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
