@extends('layouts.app')

@section('title', 'Reported Expenses: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2 style="margin: 0; margin-bottom: 4px;">Project: <span style="color: var(--accent-blue);">{{ $project->project_name }}</span></h2>
            <p style="color: var(--text-secondary); font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Reported Expenses</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-primary" onclick="toggleRecordModal()"><i class="fas fa-plus"></i> Record Expense</button>
            <a href="{{ route('admin.projects.ledger', $project->id) }}" class="btn btn-outline"><i class="fas fa-file-invoice-dollar"></i> Project Financial Ledger</a>
            <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-outline">&larr; Back to Project</a>
        </div>
    </div>

    <!-- Record Expense Modal -->
    <div id="recordExpenseModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000; padding-top: 20px;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 650px; padding: 32px; position: relative; border-radius: 16px;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleRecordModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Record Project Expense</h3>
                <p style="color: var(--accent-blue); margin: 4px 0 0;">{{ $project->project_name }}</p>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.expenses.store', $project->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="expense_category_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Expense Date</label>
                    <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Bill Image / Receipt</label>
                    <input type="file" name="bill_image" class="form-control" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top: 16px;">Save Expense</button>
            </form>
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
                        <th>Logged By</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Receipt</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalExpenses = 0; @endphp
                    @forelse($expenses as $expense)
                        @php 
                            if($expense->status === 'approved') {
                                $totalExpenses += $expense->amount;
                            }
                        @endphp
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
                            <td>
                                {{ $expense->employee->name ?? 'N/A' }}
                                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">
                                    @if($expense->recordedBy)
                                        <i class="fas fa-user-edit" style="font-size: 9px;"></i> 
                                        <strong>{{ $expense->recordedBy->role === 'admin' ? 'Admin' : 'PM' }}:</strong> {{ $expense->recordedBy->name }}
                                    @else
                                        <i class="fas fa-user-check" style="font-size: 9px;"></i> Auto-Assigned
                                    @endif
                                </div>
                            </td>
                            <td style="color: var(--danger); text-align: right; font-weight: bold; {{ ($expense->status !== 'approved') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">-Tk. {{ number_format($expense->amount, 2) }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="
                                    background: {{ $expense->status === 'approved' ? 'rgba(40, 167, 69, 0.1)' : ($expense->status === 'rejected' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)') }};
                                    color: {{ $expense->status === 'approved' ? 'var(--success)' : ($expense->status === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }};
                                    border: 1px solid {{ $expense->status === 'approved' ? 'var(--success)' : ($expense->status === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }};
                                ">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if($expense->bill_image)
                                    <a href="{{ $expense->bill_image }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 12px;">
                                        <i class="fas fa-camera"></i> Bill
                                    </a>
                                @else
                                    <span style="color: var(--text-secondary);">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown" style="text-align: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank">
                                            <i class="fas fa-file-invoice"></i> View Invoice
                                        </a>
                                        @if($expense->status === 'pending')
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="updateExpenseStatus('{{ route('admin.expenses.updateStatus', $expense->id) }}', 'approved')" style="color: var(--success);">
                                                <i class="fas fa-check-circle"></i> Approve
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="updateExpenseStatus('{{ route('admin.expenses.updateStatus', $expense->id) }}', 'rejected')" style="color: var(--danger);">
                                                <i class="fas fa-times-circle"></i> Reject
                                            </a>
                                        @endif
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="confirmExpenseDelete('{{ route('admin.expenses.destroy', $expense->id) }}')" style="color: var(--danger);">
                                             <i class="fas fa-trash-alt"></i> Delete
                                         </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">No expenses recorded for the selected dates.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align: right; font-size: 16px;">Approved Total:</th>
                            <th style="color: var(--danger); text-align: right; font-size: 16px;">Tk. {{ number_format($totalExpenses, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <form id="deleteExpenseForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <form id="updateStatusForm" method="POST" action="" style="display: none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" id="status_input">
    </form>

    <script>
        function toggleRecordModal() {
            const modal = document.getElementById('recordExpenseModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }

        function confirmExpenseDelete(url) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action will return the amount to the project balance and cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FF4C4C',
                cancelButtonColor: '#a0a0a0',
                confirmButtonText: 'Yes, delete it!',
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#fff' : '#1e1e1e',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#000' : '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteExpenseForm');
                    form.action = url;
                    form.submit();
                }
            });
        }

        function updateExpenseStatus(url, status) {
            Swal.fire({
                title: 'Confirm ' + (status.charAt(0).toUpperCase() + status.slice(1)) + '?',
                text: 'Are you sure you want to ' + status + ' this expense?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: status === 'approved' ? 'var(--success)' : 'var(--danger)',
                cancelButtonColor: '#a0a0a0',
                confirmButtonText: 'Yes, ' + status + ' it!',
                background: document.documentElement.getAttribute('data-theme') === 'light' ? '#fff' : '#1e1e1e',
                color: document.documentElement.getAttribute('data-theme') === 'light' ? '#000' : '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('updateStatusForm');
                    form.action = url;
                    document.getElementById('status_input').value = status;
                    form.submit();
                }
            });
        }
    </script>
@endsection
