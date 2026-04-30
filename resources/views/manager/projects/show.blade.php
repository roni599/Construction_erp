@extends('layouts.app')

@section('title', 'Project Ledger: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Project Ledger: {{ $project->project_name }}</h2>
            <p style="color: var(--text-secondary);">Manager: {{ Auth::user()->name }} | Client: {{ $project->client_name }}</p>
        </div>
        <a href="{{ route('manager.dashboard') }}" class="btn btn-outline">&larr; Back to Dashboard</a>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 32px;">
        <div class="glass-panel stat-card">
            <span class="stat-title">Funds Received</span>
            <span class="stat-value" style="color: var(--accent-blue);">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Expenses</span>
            <span class="stat-value" style="color: var(--danger);">Tk. {{ number_format($summary['total_expenses'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Funds Returned</span>
            <span class="stat-value" style="color: var(--accent-yellow);">Tk. {{ number_format($summary['total_manager_returns'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Project Cash Balance</span>
            <span class="stat-value" style="color: {{ $summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr; align-items: start;">
        <!-- Add Expense Form -->
        <div class="glass-panel">
            <h3>Record New Expense</h3>
            <form method="POST" action="{{ route('manager.projects.expenses.store', $project->id) }}" enctype="multipart/form-data" style="margin-top: 16px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Expense Category</label>
                    <select name="expense_category_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required max="{{ $summary['manager_cash_balance'] }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description (Optional)</label>
                    <textarea name="description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Receipt (Optional)</label>
                    <input type="file" name="bill_image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Expense</button>
            </form>

            <!-- Fund Returns moved to global menu -->
        </div>

        <div>
            <!-- Funds Received List -->
            <div class="glass-panel" style="margin-bottom: 24px;">
                <h3>Funds Received from Admin</h3>
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($funds as $fund)
                                <tr>
                                    <td>{{ $fund->fund_date->format('Y-m-d') }}</td>
                                    <td style="font-family: monospace;">
                                        <a href="{{ route('shared.funds.invoice', $fund->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;">
                                            {{ $fund->invoice_no ?? 'FND-'.$fund->id }}
                                        </a>
                                    </td>
                                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fund->payment_method) }}</td>
                                    <td style="color: var(--success);">+Tk. {{ number_format($fund->amount, 2) }}</td>
                                    <td>{{ $fund->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">No funds received yet for this project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: rgba(150, 150, 150, 0.05); font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Overall Total Received:</td>
                                <td style="color: var(--success);">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="custom-pagination" style="margin-top: 15px;">
                    {{ $funds->appends(request()->except('fund_page'))->links() }}
                </div>
            </div>

            <!-- Fund Returns List -->
            <div class="glass-panel" style="margin-bottom: 24px;">
                <div class="flex-between" style="margin-bottom: 16px;">
                    <h3 style="margin: 0;">Fund Returns to Admin</h3>
                    <a href="{{ route('manager.returns.create', ['project_id' => $project->id]) }}" class="btn btn-outline" style="padding: 6px 16px; font-size: 12px; border-color: var(--accent-yellow); color: var(--accent-yellow);">
                        <i class="fas fa-undo"></i> Record Fund Return
                    </a>
                </div>
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Received By</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $ret)
                                <tr>
                                    <td>{{ $ret->return_date->format('Y-m-d') }}</td>
                                    <td style="font-family: monospace;">
                                        <a href="{{ route('shared.returns.invoice', $ret->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;">
                                            {{ $ret->invoice_no ?? 'RET-'.$ret->id }}
                                        </a>
                                    </td>
                                    <td>{{ $ret->receivedBy->name ?? 'System Admin' }}</td>
                                    <td style="color: var(--accent-yellow);">Tk. {{ number_format($ret->amount, 2) }}</td>
                                    <td>
                                        <a href="{{ route('shared.returns.invoice', $ret->id) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; border-color: var(--accent-yellow); color: var(--accent-yellow);">
                                            <i class="fas fa-file-invoice"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">No funds returned yet for this project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: rgba(150, 150, 150, 0.05); font-weight: bold;">
                                <td colspan="3" style="text-align: right;">Overall Total Returned:</td>
                                <td style="color: var(--accent-yellow);">Tk. {{ number_format($summary['total_manager_returns'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="custom-pagination" style="margin-top: 15px;">
                    {{ $returns->appends(request()->except('return_page'))->links() }}
                </div>
            </div>
            <div class="glass-panel">
                <h3>Expenses History</h3>
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice No</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Receipt</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                                    <td style="font-family: monospace;">
                                        <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;">
                                            {{ $expense->invoice_no ?? 'EXP-'.$expense->id }}
                                        </a>
                                    </td>
                                    <td>{{ $expense->category->name }}</td>
                                    <td>{{ $expense->description ?? '-' }}</td>
                                    <td style="color: var(--danger); font-weight: 500;">Tk. {{ number_format($expense->amount, 2) }}</td>
                                    <td style="text-align: center;">
                                        @if($expense->bill_image)
                                            <a href="{{ $expense->bill_image }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; border-color: var(--accent-blue); color: var(--accent-blue);">
                                                <i class="fas fa-image"></i> View
                                            </a>
                                        @else
                                            <span style="color: var(--text-secondary); font-size: 11px;">No Image</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; border-color: var(--accent-yellow); color: var(--accent-yellow);">
                                            <i class="fas fa-file-invoice"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-secondary);">No expenses recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: rgba(150, 150, 150, 0.05); font-weight: bold;">
                                <td colspan="4" style="text-align: right;">Overall Total Expenses:</td>
                                <td style="color: var(--danger);">Tk. {{ number_format($summary['total_expenses'], 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="custom-pagination" style="margin-top: 15px;">
                    {{ $expenses->appends(request()->except('expense_page'))->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
