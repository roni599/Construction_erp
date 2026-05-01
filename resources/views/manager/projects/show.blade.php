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

    <div class="dashboard-grid" style="grid-template-columns: 1fr; align-items: start;">
        <div>
            <!-- Unified Project Ledger Header with Add Button -->
            <div class="glass-panel" style="margin-bottom: 24px;">
                <div class="flex-between" style="margin-bottom: 16px;">
                    <h3 style="margin: 0;">Unified Project Ledger</h3>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-primary" onclick="openExpenseModal()" style="padding: 8px 16px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-plus-circle"></i> Record Expense
                        </button>
                        <a href="{{ route('manager.returns.create', ['project_id' => $project->id]) }}" class="btn btn-outline" style="padding: 8px 16px; font-size: 13px; border-color: var(--accent-yellow); color: var(--accent-yellow); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-undo"></i> Return Cash
                        </a>
                    </div>
                </div>
            <!-- Unified Project Ledger -->
            <div class="glass-panel" style="margin-bottom: 24px;">
                <div class="flex-between" style="margin-bottom: 16px;">
                    <h3 style="margin: 0;">Unified Project Ledger</h3>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('manager.projects.ledger', $project->id) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 11px;">
                            <i class="fas fa-list"></i> Full Report
                        </a>
                    </div>
                </div>
                
                <div class="table-wrapper" style="margin-top: 16px;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th style="text-align: right;">Credit (In)</th>
                                <th style="text-align: right;">Debit (Out)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ledger as $item)
                                <tr>
                                    <td>
                                        <a href="{{ $item->invoice_url }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none; font-weight: 600;">
                                            {{ $item->invoice_no }}
                                        </a>
                                    </td>
                                    <td style="white-space: nowrap;">{{ $item->date->format('d M, Y') }}</td>
                                    <td>
                                        @if($item->direction === 'in')
                                            <span class="badge" style="background: rgba(0, 230, 118, 0.1); color: var(--success); font-size: 10px;">Credit</span>
                                        @else
                                            <span class="badge" style="background: rgba(255, 76, 76, 0.1); color: var(--danger); font-size: 10px;">Debit</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 13px; color: var(--text-secondary);">
                                        <span style="color: var(--text-primary); font-weight: 500;">[{{ $item->type }}]</span> {{ $item->description }}
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: var(--success);">
                                        @if($item->direction === 'in')
                                            Tk. {{ number_format($item->credit, 2) }}
                                        @else
                                            <span style="opacity: 0.2;">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: var(--danger);">
                                        @if($item->direction === 'out')
                                            Tk. {{ number_format($item->debit, 2) }}
                                        @else
                                            <span style="opacity: 0.2;">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                                        No financial transactions recorded for this project yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($ledger) > 0)
                            <tfoot>
                                <tr style="background: rgba(255,255,255,0.03); font-weight: bold; border-top: 2px solid var(--border-color);">
                                    <td colspan="4" style="text-align: right; padding: 12px;">Total Summary:</td>
                                    <td style="text-align: right; color: var(--success); padding: 12px;">
                                        Tk. {{ number_format($summary['total_manager_funds'], 2) }}
                                    </td>
                                    <td style="text-align: right; color: var(--danger); padding: 12px;">
                                        Tk. {{ number_format($summary['total_expenses'] + $summary['total_manager_returns'], 2) }}
                                    </td>
                                </tr>
                                <tr style="background: rgba(255,255,255,0.05); font-weight: 800;">
                                    <td colspan="4" style="text-align: right; padding: 12px;">Current Project Balance:</td>
                                    <td colspan="2" style="text-align: center; color: {{ $summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}; padding: 12px; font-size: 15px;">
                                        Tk. {{ number_format($summary['manager_cash_balance'], 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<div id="expenseModal" class="modal-backdrop">
    <div class="glass-panel modal-content" style="position: relative; max-width: 500px; width: 100%; margin-top: 50px;">
        <button onclick="closeExpenseModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 20px;">
            <i class="fas fa-times"></i>
        </button>
        
        <h3 style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-invoice-dollar" style="color: var(--accent-yellow);"></i> 
            Record New Expense
        </h3>
        
        <form method="POST" action="{{ route('manager.projects.expenses.store', $project->id) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Expense Category <span style="color: var(--danger);">*</span></label>
                <select name="expense_category_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Amount (Tk.) <span style="color: var(--danger);">*</span></label>
                <input type="number" step="any" name="amount" class="form-control" required 
                    placeholder="Enter amount" max="{{ $summary['manager_cash_balance'] }}" onwheel="this.blur()" autocomplete="off">
                <p style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                    Available Balance: <span style="color: var(--success); font-weight: 600;">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span>
                </p>
            </div>
            
            <div class="form-group">
                <label class="form-label">Expense Date <span style="color: var(--danger);">*</span></label>
                <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="2" placeholder="What was this for?"></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Upload Receipt (Optional)</label>
                <input type="file" name="bill_image" class="form-control" accept="image/*">
            </div>
            
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="button" class="btn btn-outline" onclick="closeExpenseModal()" style="flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 2;">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExpenseModal() {
        const modal = document.getElementById('expenseModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeExpenseModal() {
        const modal = document.getElementById('expenseModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore background scrolling
        const form = modal.querySelector('form');
        if (form) form.reset();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('expenseModal');
        if (event.target == modal) {
            closeExpenseModal();
        }
    }
</script>
@endsection
