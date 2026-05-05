@extends('layouts.app')

@section('title', 'Expense List')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>My Project Expenses</h2>
        <button class="btn btn-primary" onclick="openExpenseModal()" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus-circle"></i> Record Expense
        </button>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('manager.expenses.index') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 2; min-width: 250px;">
                <label class="form-label">Filter by Project</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All My Projects</option>
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
                <a href="{{ route('manager.expenses.index') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="glass-panel">
        <div class="table-wrapper">
            @include('manager.projects.expenses_table')
        </div>
    </div>

    <!-- Record Expense Modal -->
    <div id="expenseModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000; padding-top: 20px;">
        <div class="glass-panel animate-slide-down" style="position: relative; max-width: 500px; width: 100%; padding: 32px; border-radius: 16px;">
            <button onclick="closeExpenseModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 20px;">
                <i class="fas fa-times"></i>
            </button>
            
            <h3 style="margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-file-invoice-dollar" style="color: var(--accent-yellow);"></i> 
                Record New Expense
            </h3>
            
            <form id="globalExpenseForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Project <span style="color: var(--danger);">*</span></label>
                    <select name="project_id" id="modal_project_id" class="form-control" required style="background: rgba(0,0,0,0.8);" onchange="handleProjectChange()">
                        <option value="">-- Choose Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" data-balance="{{ $p->balance }}">
                                {{ $p->project_name }} (Bal: Tk. {{ number_format($p->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

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
                    <input type="number" step="any" name="amount" id="modal_amount" class="form-control" required 
                        placeholder="Enter amount" onwheel="this.blur()" autocomplete="off">
                    <p id="balance_info" style="font-size: 11px; color: var(--text-secondary); margin-top: 4px; display: none;">
                        Available Balance: <span id="project_balance" style="color: var(--success); font-weight: 600;"></span>
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
                
                <div class="btn-group" style="margin-top: 24px;">
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
            document.body.style.overflow = 'hidden';
        }

        function closeExpenseModal() {
            const modal = document.getElementById('expenseModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            document.getElementById('globalExpenseForm').reset();
            document.getElementById('balance_info').style.display = 'none';
        }

        function handleProjectChange() {
            const select = document.getElementById('modal_project_id');
            const selectedOption = select.options[select.selectedIndex];
            const form = document.getElementById('globalExpenseForm');
            const balanceInfo = document.getElementById('balance_info');
            const balanceSpan = document.getElementById('project_balance');
            const amountInput = document.getElementById('modal_amount');

            if (selectedOption.value) {
                const projectId = selectedOption.value;
                const balance = selectedOption.getAttribute('data-balance');
                
                // Update form action
                form.action = `/manager/projects/${projectId}/expenses`;
                
                // Update balance display
                balanceSpan.textContent = 'Tk. ' + parseFloat(balance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                amountInput.max = balance;
                balanceInfo.style.display = 'block';
            } else {
                form.action = '';
                balanceInfo.style.display = 'none';
                amountInput.removeAttribute('max');
            }
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
