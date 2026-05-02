@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2 style="margin: 0;">Projects Management</h2>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> Create Project
        </a>
    </div>

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.index') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Project Name</label>
                <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Projects</option>
                    @foreach($allProjects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Client Name</label>
                <input type="text" name="client_name" class="form-control" placeholder="Search client name..." value="{{ request('client_name') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Client Phone</label>
                <input type="text" name="client_phone" class="form-control" placeholder="Search phone..." value="{{ request('client_phone') }}">
            </div>
            <div class="form-group" style="margin: 0; flex: 1; min-width: 200px;">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" style="background: rgba(0,0,0,0.8);">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                    <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <!-- Projects Table -->
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client Name</th>
                    <th>Client Phone</th>
                    <th>Client Email</th>
                    <th>Budget</th>
                    <th>Manager</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <a href="{{ route('admin.projects.show', $project->id) }}" style="color: inherit; text-decoration: none; display: block;">
                                <strong>{{ $project->project_name }}</strong>
                                @if($project->location)
                                    <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                        <i class="fas fa-map-marker-alt" style="font-size: 10px;"></i> {{ $project->location }}
                                    </div>
                                @endif
                            </a>
                        </td>
                        <td>{{ $project->client_name }}</td>
                        <td>{{ $project->client_phone ?? '-' }}</td>
                        <td>{{ $project->client_email ?? '-' }}</td>
                        <td>Tk. {{ number_format($project->estimated_budget, 2) }}</td>
                        <td style="font-size: 14px;">
                            {{ $project->manager->name ?? 'Unassigned' }}
                            @if(isset($project->manager->phone))
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">{{ $project->manager->phone }}</div>
                            @endif
                        </td>
                        <td><span class="badge badge-{{ $project->status }}">{{ ucfirst($project->status) }}</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle" type="button">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.projects.show', $project->id) }}">
                                        <i class="fas fa-tasks"></i> Manage
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.projects.edit', $project->id) }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @if($project->status === 'running')
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="openPaymentModal({{ $project->id }}, '{{ addslashes($project->project_name) }}', {{ number_format($project->estimated_budget, 2, '.', '') }}, {{ number_format($project->clientPayments()->sum('amount'), 2, '.', '') }})">
                                        <i class="fas fa-money-bill-wave"></i> Client Payment
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="openFundModal({{ $project->id }}, '{{ addslashes($project->project_name) }}', '{{ addslashes($project->manager->name ?? 'Unassigned') }}', {{ number_format($project->estimated_budget, 2, '.', '') }}, {{ number_format($project->managerFunds()->sum('amount'), 2, '.', '') }})">
                                        <i class="fas fa-hand-holding-usd"></i> Disburse Fund
                                    </a>
                                    @endif
                                    <form id="delete-project-{{ $project->id }}" action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button class="dropdown-item text-danger" type="button" onclick="confirmDelete('delete-project-{{ $project->id }}', 'This project and its settings will be removed!')">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                            No projects found matching the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $projects->appends(request()->query())->links() }}
    </div>

    <!-- Record Client Payment Modal -->
    <div id="paymentFormModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000;">
        <div class="glass-panel animate-slide-up-custom" style="width: 100%; max-width: 500px; padding: 32px; position: relative; margin-top: 0; border-top-left-radius: 0; border-top-right-radius: 0;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="togglePaymentModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Record Client Payment</h3>
                <p id="payment-project-name" style="margin: 4px 0 0; color: var(--accent-yellow); font-weight: 500;"></p>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.payments.storeGlobal') }}" onsubmit="return validatePaymentAmount()">
                @csrf
                <input type="hidden" name="project_id" id="payment_project_id">
                
                <div class="form-group">
                    <label class="form-label">Estimated Budget</label>
                    <input type="text" id="payment_display_budget" class="form-control" readonly style="background: rgba(255,255,255,0.05); color: var(--accent-yellow);">
                    <input type="hidden" id="payment_hidden_budget" value="0">
                    <input type="hidden" id="payment_hidden_received" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="any" name="amount" id="payment_amount" class="form-control" required onwheel="this.blur()" autocomplete="off">
                    <small id="payment_amount_error" style="color: var(--danger); display: none; margin-top: 4px;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>
                <div class="btn-group" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Save Payment</button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="togglePaymentModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Disburse Fund Modal -->
    <div id="fundFormModal" class="sidebar-overlay" style="display: none; align-items: flex-start; justify-content: center; z-index: 2000;">
        <div class="glass-panel animate-slide-up-custom" style="width: 100%; max-width: 500px; padding: 32px; position: relative; margin-top: 0; border-top-left-radius: 0; border-top-right-radius: 0;">
            <button style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; transition: var(--transition);" onclick="toggleFundModal()" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-secondary)'">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 24px;">
                <h3 style="margin: 0;">Disburse Fund to Manager</h3>
                <p id="fund-project-name" style="margin: 4px 0 0; color: var(--accent-blue); font-weight: 500;"></p>
                <p id="fund-manager-name" style="margin: 2px 0 0; font-size: 13px; color: var(--text-secondary);"></p>
            </div>
            
            <form method="POST" action="{{ route('admin.projects.funds.storeGlobal') }}" onsubmit="return validateFundAmount()">
                @csrf
                <input type="hidden" name="project_id" id="fund_project_id">
                
                <div class="form-group">
                    <label class="form-label">Project Budget</label>
                    <input type="text" id="fund_display_budget" class="form-control" readonly style="background: rgba(255,255,255,0.05); color: var(--accent-blue);">
                    <input type="hidden" id="fund_hidden_budget" value="0">
                    <input type="hidden" id="fund_hidden_disbursed" value="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="any" name="amount" id="fund_amount" class="form-control" required onwheel="this.blur()" autocomplete="off">
                    <small id="fund_amount_error" style="color: var(--danger); display: none; margin-top: 4px;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Fund Date</label>
                    <input type="date" name="fund_date" class="form-control" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="mobile_banking">Mobile Banking</option>
                    </select>
                </div>
                <div class="btn-group" style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="flex: 2;">Disburse Fund</button>
                    <button type="button" class="btn btn-outline" style="flex: 1;" onclick="toggleFundModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes slideUpCustom {
            from { transform: translateY(100vh); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-slide-up-custom {
            animation: slideUpCustom 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>

    <script>
        function togglePaymentModal() {
            const modal = document.getElementById('paymentFormModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }

        function openPaymentModal(id, name, budget, totalReceived) {
            if (typeof closeAllDropdowns === 'function') closeAllDropdowns();
            document.getElementById('payment_project_id').value = id;
            document.getElementById('payment-project-name').textContent = 'Project: ' + name;
            document.getElementById('payment_display_budget').value = 'Tk. ' + new Intl.NumberFormat().format(budget);
            document.getElementById('payment_hidden_budget').value = budget;
            document.getElementById('payment_hidden_received').value = totalReceived;
            document.getElementById('payment_amount').value = '';
            document.getElementById('payment_amount_error').style.display = 'none';
            togglePaymentModal();
        }

        function validatePaymentAmount() {
            const budget = parseFloat(document.getElementById('payment_hidden_budget').value) || 0;
            const received = parseFloat(document.getElementById('payment_hidden_received').value) || 0;
            const amount = parseFloat(document.getElementById('payment_amount').value) || 0;
            const errorElement = document.getElementById('payment_amount_error');

            const remaining = Math.round((budget - received) * 100) / 100;

            if (budget > 0 && amount > (remaining + 0.001)) {
                errorElement.textContent = "Amount exceeds remaining budget (Remaining: Tk. " + new Intl.NumberFormat().format(remaining) + ")";
                errorElement.style.display = 'block';
                return false;
            }
            return true;
        }

        function toggleFundModal() {
            const modal = document.getElementById('fundFormModal');
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                setTimeout(() => modal.classList.add('active'), 10);
            } else {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }

        function openFundModal(id, name, manager, budget, totalDisbursed) {
            if (typeof closeAllDropdowns === 'function') closeAllDropdowns();
            document.getElementById('fund_project_id').value = id;
            document.getElementById('fund-project-name').textContent = 'Project: ' + name;
            document.getElementById('fund-manager-name').textContent = 'Manager: ' + manager;
            document.getElementById('fund_display_budget').value = 'Tk. ' + new Intl.NumberFormat().format(budget);
            document.getElementById('fund_hidden_budget').value = budget;
            document.getElementById('fund_hidden_disbursed').value = totalDisbursed;
            document.getElementById('fund_amount').value = '';
            document.getElementById('fund_amount_error').style.display = 'none';
            toggleFundModal();
        }

        function validateFundAmount() {
            const budget = parseFloat(document.getElementById('fund_hidden_budget').value) || 0;
            const disbursed = parseFloat(document.getElementById('fund_hidden_disbursed').value) || 0;
            const amount = parseFloat(document.getElementById('fund_amount').value) || 0;
            const errorElement = document.getElementById('fund_amount_error');

            const remaining = Math.round((budget - disbursed) * 100) / 100;

            if (budget > 0 && amount > (remaining + 0.001)) {
                errorElement.textContent = "Amount exceeds remaining budget (Remaining: Tk. " + new Intl.NumberFormat().format(remaining) + ")";
                errorElement.style.display = 'block';
                return false;
            }
            return true;
        }
    </script>
@endsection
