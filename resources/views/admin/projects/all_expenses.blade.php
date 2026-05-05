@extends('layouts.app')

@section('title', 'Project Expenses')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Project Expenses (All)</h2>
        <button class="btn btn-primary" onclick="toggleRecordModal()" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Record New Expense
        </button>
    </div>

    <!-- Record Expense Modal -->
    <div id="recordExpenseModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000; padding-top: 20px;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 650px; padding: 32px; position: relative; border-radius: 16px;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleRecordModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Record Project Expense</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.expenses.storeGlobal') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Project</label>
                    <select name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Choose a Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                        @endforeach
                    </select>
                </div>
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



    <!-- Edit Expense Modal -->
    <div id="editExpenseModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000; padding-top: 20px;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 650px; padding: 32px; position: relative; border-radius: 16px;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleEditModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Edit Project Expense</h3>
                <p id="edit-invoice-no" style="margin: 4px 0 0; font-size: 14px; color: var(--accent-yellow); font-family: monospace;"></p>
            </div>
            
            <form id="editExpenseForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <input type="text" id="edit-project-name" class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="expense_category_id" id="edit-category-id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        @foreach(\App\Models\ExpenseCategory::all() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" id="edit-amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Expense Date</label>
                    <input type="date" name="expense_date" id="edit-expense-date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Update Expense</button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.all_expenses') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 2; min-width: 250px;">
                <label class="form-label">Filter by Project</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label">Invoice No</label>
                <input type="text" name="invoice_no" class="form-control" placeholder="Search Invoice..." value="{{ request('invoice_no') }}">
            </div>
            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.projects.all_expenses') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
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
                        <th>Project Name</th>
                        <th>Category</th>
                        <th>Logged By</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @forelse($expenses as $expense)
                        @php 
                            if($expense->status === 'approved') {
                                $totalAmount += $expense->amount;
                            }
                        @endphp
                        <tr>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $expense->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $expense->project_id) }}" class="text-nowrap" style="color: var(--accent-blue); text-decoration: none;">
                                    <strong>{{ $expense->project->project_name ?? 'Deleted Project' }}</strong>
                                </a>
                            </td>
                            <td>{{ $expense->category->name ?? 'N/A' }}</td>
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
                            <td style="text-align: right; color: var(--danger); font-weight: bold; {{ ($expense->status !== 'approved') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">-Tk. {{ number_format($expense->amount, 2) }}</td>
                            <td style="text-align: center;">
                                <span class="badge" style="
                                    @if($expense->status === 'approved')
                                        background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);
                                    @elseif($expense->status === 'rejected')
                                        background: rgba(220, 53, 69, 0.1); color: var(--danger); border: 1px solid var(--danger);
                                    @else
                                        background: rgba(255, 193, 7, 0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);
                                    @endif
                                ">
                                    {{ ucfirst($expense->status ?? 'pending') }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown" style="text-align: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.projects.show', $expense->project_id) }}">
                                            <i class="fas fa-eye"></i> Show Project
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal({{ json_encode([
                                            'id' => $expense->id,
                                            'invoice_no' => $expense->invoice_no,
                                            'project_name' => $expense->project->project_name,
                                            'category_id' => $expense->expense_category_id,
                                            'amount' => $expense->amount,
                                            'expense_date' => $expense->expense_date->format('Y-m-d'),
                                            'description' => $expense->description,
                                            'update_url' => route('admin.expenses.update', $expense->id)
                                        ]) }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
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
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                                No expenses found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align: right; font-size: 16px;">Approved Total:</th>
                            <th style="text-align: right; color: var(--danger); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $expenses->appends(request()->query())->links() }}
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
        function toggleEditModal() {
            const modal = document.getElementById('editExpenseModal');
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

        function openEditModal(expense) {
            const form = document.getElementById('editExpenseForm');
            form.action = expense.update_url;
            
            document.getElementById('edit-invoice-no').textContent = expense.invoice_no;
            document.getElementById('edit-project-name').value = expense.project_name;
            document.getElementById('edit-category-id').value = expense.category_id;
            document.getElementById('edit-amount').value = expense.amount;
            document.getElementById('edit-expense-date').value = expense.expense_date;
            document.getElementById('edit-description').value = expense.description || '';
            
            toggleEditModal();
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
