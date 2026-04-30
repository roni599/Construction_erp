@extends('layouts.app')

@section('title', 'Edit Fund Disbursement: ' . $fund->invoice_no)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Edit Fund Disbursement</h2>
            <p style="color: var(--text-secondary);">Invoice: <span style="color: var(--accent-yellow); font-family: monospace;">{{ $fund->invoice_no }}</span></p>
        </div>
        <a href="{{ route('admin.projects.funds.create') }}" class="btn btn-outline">&larr; Back to Funds</a>
    </div>

    <div class="glass-panel" style="max-width: 600px; margin: 0 auto;">
        <form method="POST" action="{{ route('admin.projects.funds.update', $fund->id) }}">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Project & Manager</label>
                <select name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ old('project_id', $fund->project_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->project_name }} (Manager: {{ $p->manager->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Amount (Tk.)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', $fund->amount) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Fund Date</label>
                <input type="date" name="fund_date" class="form-control" required value="{{ old('fund_date', $fund->fund_date->format('Y-m-d')) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Method</label>
                <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                    <option value="bank_transfer" {{ old('payment_method', $fund->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cash" {{ old('payment_method', $fund->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="mobile_banking" {{ old('payment_method', $fund->payment_method) == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Note (Optional)</label>
                <textarea name="note" class="form-control" rows="3">{{ old('note', $fund->note) }}</textarea>
            </div>

            <div style="margin-top: 32px;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Update Fund Disbursement</button>
            </div>
        </form>
    </div>
@endsection
