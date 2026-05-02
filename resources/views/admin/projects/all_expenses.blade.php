@extends('layouts.app')

@section('title', 'Project Expenses')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Project Expenses (All)</h2>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Edit Expense Modal -->
    <div id="editExpenseModal" class="sidebar-overlay" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
        <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
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
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @forelse($expenses as $expense)
                        @php $totalAmount += $expense->amount; @endphp
                        <tr>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $expense->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $expense->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                    <strong>{{ $expense->project->project_name ?? 'Deleted Project' }}</strong>
                                </a>
                            </td>
                            <td>{{ $expense->category->name ?? 'N/A' }}</td>
                            <td>{{ $expense->employee->name ?? 'N/A' }}</td>
                            <td style="text-align: right; color: var(--danger); font-weight: bold;">-{{ number_format($expense->amount, 2) }}</td>
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
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                                No expenses found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($expenses) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="5" style="text-align: right; font-size: 16px;">Total:</th>
                            <th style="text-align: right; color: var(--danger); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                            <th></th>
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
            if (confirm('Are you sure you want to delete this expense? This action will return the amount to the project balance and cannot be undone.')) {
                const form = document.getElementById('deleteExpenseForm');
                form.action = url;
                form.submit();
            }
        }
    </script>
@endsection
