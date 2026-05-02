@extends('layouts.app')

@section('title', 'Record Fund Return from PM')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Record Fund Return from PM</h2>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: rgba(255, 76, 76, 0.2); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('error') }}
        </div>
    @endif

    <div class="glass-panel" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.projects.returns.storeGlobal') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Project</label>
                <select id="project_select" name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);" onchange="updateBalance()">
                    <option value="">-- Choose a Project --</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->project_name }} (Manager: {{ $p->manager->name }})</option>
                    @endforeach
                </select>
            </div>
            
            <div id="balance_info" style="display: none; margin-bottom: 20px; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid var(--accent-blue);">
                <p style="margin: 0;"><strong>Current Hand Cash:</strong> Tk. <span id="balance_display">0.00</span></p>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 4px;">
                    Full reconciliation required. Return amount must equal this balance.
                </p>
            </div>

            <div class="form-group">
                <label class="form-label">Return Amount (Tk.)</label>
                <input type="number" step="0.01" id="return_amount" name="amount" class="form-control" required readonly style="background: rgba(255,255,255,0.05); color: var(--accent-blue); font-weight: bold;">
            </div>
            <div class="form-group">
                <label class="form-label">Return Date</label>
                <input type="date" name="return_date" class="form-control" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group">
                <label class="form-label">Method</label>
                <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="mobile_banking">Mobile Banking</option>
                </select>
            </div>
            <div class="btn-group" style="margin-top: 24px;">
                <button type="submit" id="submit_btn" class="btn btn-primary" style="flex: 2; background: var(--accent-blue);" disabled>Select Project First</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline" style="flex: 1;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        const balances = @json($balances);
        
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
                submitBtn.innerText = 'Select Project First';
                return;
            }
            
            const balance = balances[projectId] || 0;
            
            balanceDisplay.innerText = Number(balance).toFixed(2);
            balanceInfo.style.display = 'block';
            amountInput.value = balance;
            
            if (balance <= 0) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'No Balance to Return';
                submitBtn.style.background = 'var(--text-secondary)';
            } else {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Record Full Return';
                submitBtn.style.background = 'var(--accent-blue)';
            }
        }
    </script>
@endsection
