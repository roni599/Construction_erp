@extends('layouts.app')

@section('title', 'Edit Client Payment: ' . $payment->invoice_no)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Edit Client Payment</h2>
            <p style="color: var(--text-secondary);">Invoice: <span style="color: var(--accent-yellow); font-family: monospace;">{{ $payment->invoice_no }}</span></p>
        </div>
        <a href="{{ route('admin.projects.payments.create') }}" class="btn btn-outline">&larr; Back to Payments</a>
    </div>

    <div class="glass-panel" style="max-width: 1300px; margin: 0 auto; padding: 40px;">
        <form method="POST" action="{{ route('admin.projects.payments.update', $payment->id) }}">
            @csrf
            @method('PUT')
            
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 0;">
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <input type="text" class="form-control" value="{{ $payment->project->project_name }}" disabled style="background: rgba(255,255,255,0.05);">
                </div>

                <div class="form-group">
                    <label class="form-label">Amount (Tk.)</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', $payment->amount) }}">
                </div>
            </div>

            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 0;">
                <div class="form-group">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Method</label>
                    <select name="payment_method" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="bank_transfer" {{ old('payment_method', $payment->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="cash" {{ old('payment_method', $payment->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="cheque" {{ old('payment_method', $payment->payment_method) == 'cheque' ? 'selected' : '' }}>Cheque</option>
                        <option value="mobile_banking" {{ old('payment_method', $payment->payment_method) == 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 8px;">
                <label class="form-label">Note (Optional)</label>
                <textarea name="note" class="form-control" rows="3">{{ old('note', $payment->note) }}</textarea>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 48px;">Update Payment Record</button>
            </div>
        </form>
    </div>
@endsection
