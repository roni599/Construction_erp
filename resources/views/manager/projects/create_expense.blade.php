@extends('layouts.app')

@section('title', 'Record New Expense')

@section('content')
    <div class="flex-between" style="margin-bottom: 0;">
        <div>
            <h2>Record Project Expense</h2>
        </div>
        <a href="{{ route('manager.projects.index') }}" class="btn btn-outline">&larr; Back to Projects</a>
    </div>

    <div class="glass-panel" style="max-width: 1250px; margin: 0 auto; padding: 40px;">
        <form method="POST" action="{{ route('manager.expenses.storeGlobal') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 10px;">
                <div class="form-group">
                    <label class="form-label">Select Project <span style="color: var(--danger);">*</span></label>
                    <select name="project_id" id="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Select Project --</option>
                        @foreach($projects->where('status', 'running') as $p)
                            <option value="{{ $p->id }}" {{ (isset($selectedProject) && $selectedProject->id == $p->id) || old('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }} ({{ $p->client_name }})
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
            </div>

            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 10px;">
                <div class="form-group">
                    <label class="form-label">Amount (Tk.) <span style="color: var(--danger);">*</span></label>
                    <input type="number" step="any" name="amount" class="form-control" required placeholder="Enter amount" onwheel="this.blur()">
                    <div id="balance-info" style="margin-top: 8px;">
                        @if($summary)
                            <p style="font-size: 13px; color: var(--text-secondary);">
                                Available Balance: <span style="color: var(--success); font-weight: 600;">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Expense Date <span style="color: var(--danger);">*</span></label>
                    <input type="date" name="expense_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Describe the expense details..."></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Upload Receipt (Optional)</label>
                <div style="border: 2px dashed var(--border-color); border-radius: 12px; padding: 20px; text-align: center; background: rgba(255,255,255,0.02);">
                    <input type="file" name="bill_image" class="form-control" accept="image/*" style="border: none; background: transparent;">
                    <p style="font-size: 12px; color: var(--text-secondary); margin-top: 8px;">Accepted formats: JPG, PNG, WEBP (Max 5MB)</p>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <button type="submit" class="btn btn-primary" style="flex: 2; padding: 14px;">
                    <i class="fas fa-check-circle"></i> Save Transaction
                </button>
                <a href="{{ route('manager.projects.index') }}" class="btn btn-outline" style="flex: 1; padding: 14px;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        // Optional: Real-time balance check when project changes
        document.getElementById('project_id').addEventListener('change', function() {
            const projectId = this.value;
            const balanceInfo = document.getElementById('balance-info');
            
            if (!projectId) {
                balanceInfo.innerHTML = '';
                return;
            }

            // In a real app, you might want to fetch balance via AJAX
            // For now, we can redirect or show a message
            balanceInfo.innerHTML = '<p style="font-size: 12px; color: var(--accent-yellow);">Checking balance...</p>';
            
            // Redirect to refresh balance info
            window.location.href = "{{ route('manager.expenses.create') }}?project_id=" + projectId;
        });
    </script>
@endsection
