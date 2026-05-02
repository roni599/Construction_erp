@extends('layouts.app')

@section('title', 'Disburse Funds to Manager')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2 style="margin: 0;">Disburse Funds to Manager</h2>
        <button class="btn btn-primary" onclick="toggleForm()" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Record Disburse Fund
        </button>
    </div>


    <!-- Record Fund Modal -->
    <div id="fundFormModal" class="sidebar-overlay" style="display: {{ $errors->any() ? 'flex' : 'none' }}; align-items: flex-start; justify-content: center; padding-top: 50px; z-index: 2000;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleForm()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Disburse Fund to Manager</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.funds.storeGlobal') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Project</label>
                    <select name="project_id" id="project_select" class="form-control" required style="background: rgba(0,0,0,0.8);" onchange="updateBudgetInfo()">
                        <option value="" data-budget="0">-- Choose a Project --</option>
                        @foreach($projects->where('status', 'running') as $p)
                            <option value="{{ $p->id }}" data-budget="{{ $p->estimated_budget }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }} (Manager: {{ $p->manager->name ?? 'Unassigned' }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" id="hidden_estimated_budget" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="any" name="amount" id="fund_amount" class="form-control" required value="{{ old('amount') }}" onwheel="this.blur()" autocomplete="off">
                    <small id="amount_error" style="color: var(--danger); display: none; margin-top: 4px;">Amount cannot exceed estimated budget.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Fund Date</label>
                    <input type="date" name="fund_date" class="form-control" required value="{{ old('fund_date', date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mobile_banking" {{ old('payment_method') == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                    </select>
                </div>
                <div class="btn-group" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Disburse Fund</button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="toggleForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Fund Modal -->
    <div id="editFundModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; padding-top: 50px; z-index: 2000;">
        <div class="glass-panel animate-slide-down" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleEditModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Edit Fund Disbursement</h3>
                <p id="edit-invoice-no" style="margin: 4px 0 0; font-size: 14px; color: var(--accent-yellow); font-family: monospace;"></p>
            </div>
            
            <form id="editFundForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <select name="project_id" id="edit-project-id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->project_name }} (Manager: {{ $p->manager->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" id="edit-amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Fund Date</label>
                    <input type="date" name="fund_date" id="edit-fund-date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" id="edit-payment-method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Note (Optional)</label>
                    <textarea name="note" id="edit-note" class="form-control" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Update Fund</button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.funds.create') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 2; min-width: 250px;">
                <label class="form-label">Filter by Project</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Projects</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }} ({{ $p->manager->name }})
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
                <a href="{{ route('admin.projects.funds.create') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Funds Table -->
    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Manager</th>
                        <th>Method</th>
                        <th>Given By</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @forelse($funds as $fund)
                        @php $totalAmount += $fund->amount; @endphp
                        <tr>
                            <td>{{ $fund->fund_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('admin.projects.funds.invoice', $fund->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $fund->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $fund->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                    <strong>{{ $fund->project->project_name }}</strong>
                                </a>
                            </td>
                            <td>{{ $fund->project->manager->name ?? 'N/A' }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fund->payment_method) }}</td>
                            <td>{{ $fund->givenBy->name ?? 'Unknown' }}</td>
                            <td style="text-align: right; color: var(--accent-blue); font-weight: bold;">+{{ number_format($fund->amount, 2) }}</td>
                            <td>
                                <div class="dropdown" style="text-align: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.projects.funds.show', $fund->id) }}">
                                            <i class="fas fa-eye"></i> Show
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal({{ json_encode([
                                            'id' => $fund->id,
                                            'invoice_no' => $fund->invoice_no,
                                            'project_id' => $fund->project_id,
                                            'amount' => $fund->amount,
                                            'fund_date' => $fund->fund_date->format('Y-m-d'),
                                            'payment_method' => $fund->payment_method,
                                            'note' => $fund->note,
                                            'budget' => $fund->project->estimated_budget,
                                            'total_disbursed' => \App\Models\ManagerFund::where('project_id', $fund->project_id)->sum('amount'),
                                            'update_url' => route('admin.projects.funds.update', $fund->id)
                                        ]) }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.projects.funds.invoice', $fund->id) }}" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                                No disbursed funds found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($funds) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align: right; font-size: 16px;">Total:</th>
                            <th style="text-align: right; color: var(--accent-blue); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <script>
        function toggleForm() {
            const modal = document.getElementById('fundFormModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                    if (document.getElementById('amount_error')) {
                        document.getElementById('amount_error').style.display = 'none';
                    }
                }, 300);
            }
        }

        function toggleEditModal() {
            const modal = document.getElementById('editFundModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                    if (document.getElementById('edit_amount_error')) {
                        document.getElementById('edit_amount_error').style.display = 'none';
                    }
                }, 300);
            }
        }

        function openEditModal(fund) {
            const form = document.getElementById('editFundForm');
            form.action = fund.update_url;
            
            document.getElementById('edit-invoice-no').textContent = fund.invoice_no;
            document.getElementById('edit-project-id').value = fund.project_id;
            document.getElementById('edit-amount').value = fund.amount;
            document.getElementById('edit-fund-date').value = fund.fund_date;
            document.getElementById('edit-payment-method').value = fund.payment_method;
            document.getElementById('edit-note').value = fund.note || '';
            
            // Set values for validation
            document.getElementById('edit-budget-val').value = fund.budget || 0;
            document.getElementById('edit-other-disbursed-val').value = (fund.total_disbursed || 0) - (fund.amount || 0);
            
            toggleEditModal();
        }

        function validateEditAmount() {
            const budget = parseFloat(document.getElementById('edit-budget-val').value) || 0;
            const otherDisbursed = parseFloat(document.getElementById('edit-other-disbursed-val').value) || 0;
            const amount = parseFloat(document.getElementById('edit-amount').value) || 0;
            const errorElement = document.getElementById('edit_amount_error');

            const remaining = Math.round((budget - otherDisbursed) * 100) / 100;

            if (budget > 0 && amount > (remaining + 0.001)) {
                errorElement.textContent = "Amount exceeds remaining budget (Tk. " + new Intl.NumberFormat().format(remaining) + ")";
                errorElement.style.display = 'block';
                return false;
            }
            errorElement.style.display = 'none';
            return true;
        }

        function updateBudgetInfo() {
            const select = document.getElementById('project_select');
            const selectedOption = select.options[select.selectedIndex];
            const budget = parseFloat(selectedOption.getAttribute('data-budget')) || 0;
            const hiddenInput = document.getElementById('hidden_estimated_budget');

            hiddenInput.value = budget;
        }

        function validateFundAmount() {
            const budget = parseFloat(document.getElementById('hidden_estimated_budget').value) || 0;
            const amount = parseFloat(document.getElementById('fund_amount').value) || 0;
            const errorElement = document.getElementById('amount_error');

            if (budget > 0 && amount > (budget + 0.001)) {
                errorElement.style.display = 'block';
                toastr.error("Amount exceeds estimated budget!");
                return false;
            }
            errorElement.style.display = 'none';
            return true;
        }

        // Initialize if old value exists
        window.addEventListener('load', function() {
            if (document.getElementById('project_select').value) {
                updateBudgetInfo();
            }
        });
    </script>
@endsection
