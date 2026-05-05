@extends('layouts.app')

@section('title', 'Project Ledger: ' . $project->project_name)

@section('content')
    <style>
        @media print {
            .table-wrapper { overflow: visible !important; }
            .table { border-collapse: collapse !important; width: 100% !important; }
            .table thead { display: table-header-group !important; }
            .table tfoot { display: table-row-group !important; }
            .table tr { page-break-inside: avoid !important; }
            .table td, .table th { page-break-inside: avoid !important; border: 1px solid #ddd !important; }
            .no-print, .btn, .glass-panel:has(form), form { display: none !important; }
            .main-content { padding: 0 !important; }
            .glass-panel { border: none !important; background: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Project: <span style="color: var(--accent-blue);">{{ $project->project_name }}</span></h2>
            <p style="color: var(--text-secondary); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Project Financial Ledger</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.projects.expenses', $project->id) }}" class="btn btn-outline"><i class="fas fa-receipt"></i> Reported Expenses</a>
            <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-outline">&larr; Back to Project</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.ledger', $project->id) }}" style="display: flex; gap: 16px; align-items: end;">
            <div class="form-group" style="margin: 0; flex: 1;">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1;">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1;">
                <label class="form-label">Invoice No</label>
                <input type="text" name="invoice_no" class="form-control" placeholder="Search Txn ID..." value="{{ request('invoice_no') }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('admin.projects.ledger', $project->id) }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Txn ID</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th style="text-align: center;">Status</th>
                        <th style="color: var(--success); text-align: right; white-space: nowrap;">Credit (In)</th>
                        <th style="color: var(--danger); text-align: right; white-space: nowrap;">Debit (Out)</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalCredit = 0;
                        $totalDebit = 0;
                    @endphp
                    @forelse($ledger as $entry)
                        @php 
                            $totalCredit += $entry['credit'];
                            $totalDebit += $entry['debit'];
                        @endphp
                        <tr>
                            <td>{{ $entry['date'] }}</td>
                            <td style="font-family: monospace;">
                                @if($entry['type'] == 'Client Payment')
                                    <a href="{{ route('shared.payments.invoice', $entry['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $entry['id'] }}</a>
                                @elseif($entry['type'] == 'Fund Disbursed')
                                    <a href="{{ route('shared.funds.invoice', $entry['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $entry['id'] }}</a>
                                @elseif(str_contains($entry['type'], 'Expense'))
                                    <a href="{{ route('shared.expenses.invoice', $entry['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $entry['id'] }}</a>
                                @elseif($entry['type'] == 'Fund Returned by PM')
                                    <a href="{{ route('shared.returns.invoice', $entry['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">{{ $entry['id'] }}</a>
                                @else
                                    {{ $entry['id'] }}
                                @endif
                            </td>
                            <td><strong>{{ $entry['type'] }}</strong></td>
                            <td>{{ $entry['description'] }}</td>
                            <td style="text-align: center;">
                                @if(isset($entry['status']))
                                    <span class="badge" style="
                                        padding: 4px 10px; font-size: 11px; font-weight: 600;
                                        @if($entry['status'] === 'approved')
                                            background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);
                                        @elseif($entry['status'] === 'rejected')
                                            background: rgba(220, 53, 69, 0.1); color: var(--danger); border: 1px solid var(--danger);
                                        @else
                                            background: rgba(255, 193, 7, 0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);
                                        @endif
                                    ">
                                        {{ ucfirst($entry['status']) }}
                                    </span>
                                @else
                                    <span style="color: var(--text-secondary); opacity: 0.6;">-</span>
                                @endif
                            </td>
                            <td style="color: var(--success); text-align: right; white-space: nowrap;">
                                {{ $entry['credit'] != 0 ? 'Tk.'.number_format($entry['credit'], 2) : '-' }}
                            </td>
                            <td style="color: var(--danger); text-align: right; white-space: nowrap; {{ (isset($entry['status']) && $entry['status'] === 'rejected') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                @if(isset($entry['amount']))
                                    Tk.{{ number_format($entry['amount'], 2) }}
                                @else
                                    {{ $entry['debit'] != 0 ? 'Tk.'.number_format($entry['debit'], 2) : '-' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 24px;">No ledger entries found for the selected dates.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($ledger) > 0)
                    <tfoot>
                        <tr style="background: rgba(255, 255, 255, 0.05); font-weight: bold; border-top: 2px solid var(--border-color);">
                            <th colspan="5" style="text-align: right; font-size: 15px; padding: 12px;">PROJECT TOTALS (Payments - Expenses):</th>
                            <th style="color: var(--success); text-align: right; font-size: 15px; padding: 12px;">
                                Tk. {{ number_format($summary['total_client_payments'], 2) }}
                                <div style="font-size: 10px; font-weight: normal; color: var(--text-secondary); margin-top: 4px;">Total Received</div>
                            </th>
                            <th style="color: var(--danger); text-align: right; font-size: 15px; padding: 12px;">
                                Tk. {{ number_format($summary['total_expenses'], 2) }}
                                <div style="font-size: 10px; font-weight: normal; color: var(--text-secondary); margin-top: 4px;">Total Spent</div>
                            </th>
                        </tr>
                        <tr style="background: rgba(255, 255, 255, 0.1); font-weight: bold;">
                            <th colspan="5" style="text-align: right; font-size: 16px; padding: 12px;">NET PROFIT / LOSS:</th>
                            <th colspan="2" style="text-align: center; font-size: 18px; padding: 12px; color: {{ ($summary['total_client_payments'] - $summary['total_expenses']) >= 0 ? 'var(--success)' : 'var(--danger)' }}">
                                Tk. {{ number_format($summary['total_client_payments'] - $summary['total_expenses'], 2) }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
