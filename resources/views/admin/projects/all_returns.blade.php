@extends('layouts.app')

@section('title', 'Fund Received List (Returns)')

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <h2>Fund Received List (from PM)</h2>
    </div>

    @if(session('success'))
        <div style="background: rgba(0, 230, 118, 0.2); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('admin.projects.all_returns') }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
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
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label">Invoice No</label>
                <input type="text" name="invoice_no" class="form-control" placeholder="Search Invoice..." value="{{ request('invoice_no') }}">
            </div>
            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('admin.projects.all_returns') }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
        </form>
    </div>

    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Returned By</th>
                        <th>Received By</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalAmount = 0; @endphp
                    @forelse($returns as $return)
                        @php $totalAmount += $return->amount; @endphp
                        <tr>
                            <td>{{ $return->return_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('shared.returns.invoice', $return->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $return->invoice_no ?? 'RET-'.$return->id }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.projects.show', $return->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                                    <strong>{{ $return->project->project_name ?? 'Deleted Project' }}</strong>
                                </a>
                            </td>
                            <td>{{ $return->employee->name ?? 'N/A' }}</td>
                            <td>{{ $return->receivedBy->name ?? 'N/A' }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $return->payment_method) }}</td>
                            <td style="text-align: right; color: var(--success); font-weight: bold;">+{{ number_format($return->amount, 2) }}</td>
                            <td>
                                <div class="dropdown" style="text-align: center;">
                                    <button class="dropdown-toggle" type="button">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="{{ route('admin.projects.show', $return->project_id) }}">
                                            <i class="fas fa-eye"></i> Show Project
                                        </a>
                                        <a class="dropdown-item" href="{{ route('shared.returns.invoice', $return->id) }}" target="_blank">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                                No returns found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($returns) > 0)
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

    <div class="custom-pagination" style="margin-top: 24px;">
        {{ $returns->appends(request()->query())->links() }}
    </div>
@endsection
