@extends('layouts.app')

@section('title', 'Reported Expenses: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Project: <span style="color: var(--accent-blue);">{{ $project->project_name }}</span></h2>
            <p style="color: var(--text-secondary); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Reported Expenses</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.projects.ledger', $project->id) }}" class="btn btn-outline"><i class="fas fa-file-invoice-dollar"></i> Project Financial Ledger</a>
            <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-outline">&larr; Back to Project</a>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.expenses', $project->id) }}" style="display: flex; gap: 16px; align-items: end;">
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
                <input type="text" name="invoice_no" class="form-control" placeholder="Search Invoice..." value="{{ request('invoice_no') }}">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('admin.projects.expenses', $project->id) }}" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: center;">Receipt</th>
                        <th style="text-align: center;">Invoice</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalExpenses = 0; @endphp
                    @forelse($expenses as $expense)
                        @php $totalExpenses += $expense->amount; @endphp
                        <tr>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;">
                                    {{ $expense->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge" style="background: rgba(255,215,0,0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);">
                                    <i class="fas fa-tag" style="margin-right: 4px;"></i> {{ $expense->category->name }}
                                </span>
                            </td>
                            <td>{{ $expense->description ?? '-' }}</td>
                            <td style="color: var(--danger); text-align: right;">-Tk. {{ number_format($expense->amount, 2) }}</td>
                            <td style="text-align: center;">
                                @if($expense->bill_image)
                                    <a href="{{ $expense->bill_image }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">
                                        <i class="fas fa-camera"></i> Bill
                                    </a>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px; border-color: var(--accent-blue); color: var(--accent-blue);">
                                    <i class="fas fa-file-invoice"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 24px;">No expenses recorded for the selected dates.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align: right; font-size: 16px;">Total Expenses:</th>
                            <th style="color: var(--danger); text-align: right; font-size: 16px;">Tk. {{ number_format($totalExpenses, 2) }}</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
