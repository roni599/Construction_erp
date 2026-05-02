@extends('layouts.app')

@section('title', 'Fund Returns to Admin')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2 style="margin: 0;">Fund Returns to Admin</h2>
        </div>
        <button onclick="openReturnModal()" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
            <i class="fas fa-undo"></i> Record Fund Return
        </button>
    </div>
    
    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('manager.returns.create') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
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
                <a href="{{ route('manager.returns.create') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>


    <div class="glass-panel">
        <h3 style="margin-bottom: 20px;">Return History</h3>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Returned To (Admin)</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                        <th style="text-align: center;">Action</th>
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
                            <td>{{ $ret->project->project_name }}</td>
                            <td>{{ $ret->receivedBy->name ?? 'N/A' }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $ret->payment_method) }}</td>
                            <td style="text-align: right; color: var(--accent-yellow); font-weight: bold;">
                                Tk. {{ number_format($ret->amount, 2) }}
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('shared.returns.invoice', $ret->id) }}" target="_blank" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-color: var(--accent-yellow); color: var(--accent-yellow);">
                                    <i class="fas fa-file-invoice"></i> View Receipt
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 40px;">No return records found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: rgba(150, 150, 150, 0.05); font-weight: bold;">
                        <td colspan="5" style="text-align: right;">Total Returned (This List):</td>
                        <td style="color: var(--accent-yellow); text-align: right;">
                            Tk. {{ number_format($returns->sum('amount'), 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $returns->appends(request()->query())->links() }}
    </div>

    <!-- Return Modal -->
    <div id="returnModal" class="modal-backdrop">
        <div class="modal-content glass-panel" style="max-width: 1000px; width: 95vw; padding: 24px; position: relative;">
            <button onclick="closeReturnModal()" class="modal-close-btn" style="position: absolute; top: 20px; right: 20px; background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:28px; transition: var(--transition); z-index: 9999;">
                <i class="fas fa-times"></i>
            </button>
            <div style="margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <h3 style="margin: 0; font-size: 20px;">Record Fund Return</h3>
            </div>
            <form method="POST" action="{{ route('manager.returns.storeGlobal') }}">
                @csrf
                <div class="responsive-grid">
                    <!-- Left Column -->
                    <div>
                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="form-label" style="font-size: 14px;">Select Project</label>
                            <select id="project_select" name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);" onchange="updateBalance()">
                                <option value="">-- Choose a Project --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="balance_info" style="display: none; margin-bottom: 24px; padding: 20px; background: rgba(255, 215, 0, 0.05); border-radius: 12px; border: 1px dashed rgba(255, 215, 0, 0.4);">
                            <p style="margin: 0; color: var(--text-primary); font-size: 14px;"><strong>Cash Balance in Hand:</strong></p>
                            <p style="color: var(--accent-yellow); font-size: 20px; font-weight: 700; margin-top: 4px;">Tk. <span id="balance_display">0.00</span></p>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="form-label" style="font-size: 14px;">Return Amount (Tk.)</label>
                            <input type="number" step="0.01" id="return_amount" name="amount" class="form-control" required readonly style="background: rgba(255,255,255,0.05); color: var(--accent-yellow); font-weight: 700;">
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="responsive-grid" style="gap: 24px;">
                            <div class="form-group" style="margin-bottom: 24px;">
                                <label class="form-label" style="font-size: 14px;">Return Date</label>
                                <input type="date" name="return_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>

                            <div class="form-group" style="margin-bottom: 24px;">
                                <label class="form-label" style="font-size: 14px;">Payment Method</label>
                                <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" style="font-size: 14px;">Received By (Admin)</label>
                            <select name="received_by" class="form-control" required style="background: rgba(0,0,0,0.8); height: 44px; font-size: 14px;">
                                <option value="">-- Select Admin --</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label" style="font-size: 14px;">Notes / Handover Details (Optional)</label>
                            <textarea name="note" class="form-control" rows="1" style="background: rgba(0,0,0,0.5); height: 44px; padding: 10px; font-size: 14px;" placeholder="Briefly describe the fund return..."></textarea>
                        </div>

                    </div>
                </div>
                
                <div class="btn-group" style="margin-top: 32px;">
                    <button type="button" onclick="closeReturnModal()" class="btn btn-outline" style="flex: 1;">Cancel</button>
                    <button type="submit" id="submit_btn" class="btn btn-primary" style="flex: 2;" disabled>Record Return</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const balances = @json($balances);
        
        function openReturnModal() {
            document.getElementById('returnModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeReturnModal() {
            document.getElementById('returnModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function updateBalance() {
            const select = document.getElementById('project_select');
            const projectId = select.value;
            const amountInput = document.getElementById('return_amount');
            const balanceInfo = document.getElementById('balance_info');
            const balanceDisplay = document.getElementById('balance_display');
            const submitBtn = document.getElementById('submit_btn');
            
            if (!projectId) {
                balanceInfo.style.display = 'none';
                amountInput.value = '';
                submitBtn.disabled = true;
                submitBtn.innerText = 'Select Project';
                return;
            }
            
            const balance = balances[projectId] || 0;
            
            balanceDisplay.innerText = Number(balance).toLocaleString('en-US', {minimumFractionDigits: 2});
            balanceInfo.style.display = 'block';
            amountInput.value = balance;
            
            if (balance <= 0) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'No Balance to Return';
                submitBtn.style.background = 'var(--text-secondary)';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Confirm Full Return';
                submitBtn.style.background = 'var(--accent-yellow)';
            }
        }

        // Handle pre-selected project from URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const projectId = urlParams.get('project_id');
            
            if (projectId) {
                const select = document.getElementById('project_select');
                select.value = projectId;
                updateBalance();
                openReturnModal();
            }
        }

        // Close modal on click outside
        window.onclick = function(event) {
            const modal = document.getElementById('returnModal');
            if (event.target == modal) {
                closeReturnModal();
            }
        }
    </script>
@endsection
