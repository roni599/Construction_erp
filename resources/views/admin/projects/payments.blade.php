@extends('layouts.app')

@section('title', 'Client Payments')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <h2 style="margin: 0;">Client Payments</h2>
        <button class="btn btn-primary" onclick="toggleForm()" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Record New Payment
        </button>
    </div>


    <!-- Record Payment Modal -->
    <div id="paymentFormModal" class="sidebar-overlay" style="display: {{ $errors->any() ? 'flex' : 'none' }}; align-items: center; justify-content: center; z-index: 2000;">
        <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleForm()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Record Client Payment</h3>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.payments.storeGlobal') }}" onsubmit="return validatePaymentAmount()">
                @csrf
                <div class="form-group">
                    <label class="form-label">Select Project</label>
                    <select name="project_id" id="project_select" class="form-control" required style="background: rgba(0,0,0,0.8);" onchange="updateBudgetInfo()">
                        <option value="" data-budget="0">-- Choose a Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" data-budget="{{ $p->estimated_budget }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }} ({{ $p->client_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div id="budget_info_container" class="form-group" style="display: none;">
                    <label class="form-label">Estimated Budget</label>
                    <input type="text" id="display_estimated_budget" class="form-control" readonly style="background: rgba(255,255,255,0.05); color: var(--accent-yellow);">
                    <input type="hidden" id="hidden_estimated_budget" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="any" name="amount" id="payment_amount" class="form-control" required value="{{ old('amount') }}" onwheel="this.blur()" autocomplete="off">
                    <small id="amount_error" style="color: var(--danger); display: none; margin-top: 4px;">Amount cannot exceed estimated budget.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ old('payment_date', date('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="mobile_banking" {{ old('payment_method') == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Save Payment</button>
            </form>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div id="editPaymentModal" class="sidebar-overlay" style="display: none; align-items: center; justify-content: center; z-index: 2000;">
        <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 32px; position: relative;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleEditModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Edit Client Payment</h3>
                <p id="edit-invoice-no" style="margin: 4px 0 0; font-size: 14px; color: var(--accent-yellow); font-family: monospace;"></p>
            </div>
            
            <form id="editPaymentForm" method="POST" action="" onsubmit="return validateEditAmount()">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-budget-val">
                <input type="hidden" id="edit-other-received-val">
                
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <input type="text" id="edit-project-name" class="form-control" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" id="edit-amount" class="form-control" required>
                    <small id="edit_amount_error" style="color: var(--danger); display: none; margin-top: 4px;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" id="edit-payment-date" class="form-control" required>
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
                <button type="submit" class="btn btn-primary" style="width:100%;">Update Payment</button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.payments.create') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
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
            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.projects.payments.create') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Payments Table -->
    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Method</th>
                        <th>Recorded By</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @forelse($payments as $payment)
                        @php $totalAmount += $payment->amount; @endphp
                        <tr>
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('admin.projects.payments.invoice', $payment->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $payment->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $payment->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                    <strong>{{ $payment->project->project_name }}</strong>
                                </a>
                            </td>
                            <td>{{ $payment->project->client_name }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                            <td>{{ $payment->recordedBy->name }}</td>
                            <td style="text-align: right; color: var(--success); font-weight: bold;">+{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <div class="dropdown" style="text-align: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.projects.payments.show', $payment->id) }}">
                                            <i class="fas fa-eye"></i> Show
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal({{ json_encode([
                                            'id' => $payment->id,
                                            'invoice_no' => $payment->invoice_no,
                                            'project_name' => $payment->project->project_name,
                                            'amount' => $payment->amount,
                                            'payment_date' => $payment->payment_date->format('Y-m-d'),
                                            'payment_method' => $payment->payment_method,
                                            'note' => $payment->note,
                                            'budget' => $payment->project->estimated_budget,
                                            'total_received' => \App\Models\ClientPayment::where('project_id', $payment->project_id)->sum('amount'),
                                            'update_url' => route('admin.projects.payments.update', $payment->id)
                                        ]) }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="{{ route('admin.projects.payments.invoice', $payment->id) }}" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                                No client payments found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($payments) > 0)
                    <tfoot>
                        <tr>
                            <th colspan="6" style="text-align: right; font-size: 16px;">Total:</th>
                            <th style="text-align: right; color: var(--success); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <script>
        function toggleForm() {
            const modal = document.getElementById('paymentFormModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.style.display = 'none';
                    const form = modal.querySelector('form');
                    if (form) form.reset();
                    if (document.getElementById('budget_info_container')) {
                        document.getElementById('budget_info_container').style.display = 'none';
                    }
                    if (document.getElementById('amount_error')) {
                        document.getElementById('amount_error').style.display = 'none';
                    }
                }, 300);
            }
        }

        function toggleEditModal() {
            const modal = document.getElementById('editPaymentModal');
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

        function openEditModal(payment) {
            const form = document.getElementById('editPaymentForm');
            form.action = payment.update_url;
            
            document.getElementById('edit-invoice-no').textContent = payment.invoice_no;
            document.getElementById('edit-project-name').value = payment.project_name;
            document.getElementById('edit-amount').value = payment.amount;
            document.getElementById('edit-payment-date').value = payment.payment_date;
            document.getElementById('edit-payment-method').value = payment.payment_method;
            document.getElementById('edit-note').value = payment.note || '';
            
            // Set values for validation
            document.getElementById('edit-budget-val').value = payment.budget || 0;
            document.getElementById('edit-other-received-val').value = (payment.total_received || 0) - (payment.amount || 0);
            
            toggleEditModal();
        }

        function validateEditAmount() {
            const budget = parseFloat(document.getElementById('edit-budget-val').value) || 0;
            const otherReceived = parseFloat(document.getElementById('edit-other-received-val').value) || 0;
            const amount = parseFloat(document.getElementById('edit-amount').value) || 0;
            const errorElement = document.getElementById('edit_amount_error');

            const remaining = Math.round((budget - otherReceived) * 100) / 100;

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
            const container = document.getElementById('budget_info_container');
            const displayInput = document.getElementById('display_estimated_budget');
            const hiddenInput = document.getElementById('hidden_estimated_budget');

            if (budget > 0) {
                container.style.display = 'block';
                displayInput.value = 'Tk. ' + new Intl.NumberFormat().format(budget);
                hiddenInput.value = budget;
            } else {
                container.style.display = 'none';
                hiddenInput.value = 0;
            }
        }

        function validatePaymentAmount() {
            const budget = parseFloat(document.getElementById('hidden_estimated_budget').value) || 0;
            const amount = parseFloat(document.getElementById('payment_amount').value) || 0;
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
