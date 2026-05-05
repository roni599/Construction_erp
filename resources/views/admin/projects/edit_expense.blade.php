@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Edit Expense: <span style="color: var(--accent-yellow);">{{ $expense->invoice_no }}</span></h2>
            <p style="color: var(--text-secondary);">Project: {{ $expense->project->project_name }} | Recorded By: @if($expense->recordedBy) <strong>{{ $expense->recordedBy->role === 'admin' ? 'Admin' : 'PM' }}:</strong> {{ $expense->recordedBy->name }} @else Auto-Assigned @endif</p>
        </div>
        <a href="{{ route('admin.projects.all_expenses') }}" class="btn btn-outline">&larr; Back to List</a>
    </div>

    <div class="glass-panel" style="max-width: 600px;">
        <form method="POST" action="{{ route('admin.expenses.update', $expense->id) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Expense Category</label>
                <select name="expense_category_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $expense->expense_category_id == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Amount (Tk.)</label>
                <input type="number" step="0.01" name="amount" class="form-control" required value="{{ old('amount', $expense->amount) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Expense Date</label>
                <input type="date" name="expense_date" class="form-control" required value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $expense->description) }}</textarea>
            </div>

            <div style="margin-top: 32px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Update Expense</button>
                <a href="{{ route('admin.projects.all_expenses') }}" class="btn btn-outline" style="flex: 1; text-align: center;">Cancel</a>
            </div>
        </form>
    </div>
@endsection
