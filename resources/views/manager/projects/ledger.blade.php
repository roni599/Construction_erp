@extends('layouts.app')

@section('title', 'Project Ledger: ' . $project->project_name)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Project Ledger: {{ $project->project_name }}</h2>
            <p style="color: var(--text-secondary);">Client: {{ $project->client_name }} | Status: <span class="badge badge-{{ $project->status }}">{{ $project->status }}</span></p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('manager.projects.show', $project->id) }}" class="btn btn-outline">Record Expense</a>
            <a href="{{ route('manager.dashboard') }}" class="btn btn-outline">&larr; Back</a>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 32px;">
        <div class="glass-panel stat-card">
            <span class="stat-title">Funds Received</span>
            <span class="stat-value" style="color: var(--accent-blue);">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Total Expenses</span>
            <span class="stat-value" style="color: var(--danger);">Tk. {{ number_format($summary['total_expenses'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Funds Returned</span>
            <span class="stat-value" style="color: var(--accent-yellow);">Tk. {{ number_format($summary['total_manager_returns'], 2) }}</span>
        </div>
        <div class="glass-panel stat-card">
            <span class="stat-title">Project Cash Balance</span>
            <span class="stat-value" style="color: {{ $summary['manager_cash_balance'] >= 0 ? 'var(--success)' : 'var(--danger)' }}">Tk. {{ number_format($summary['manager_cash_balance'], 2) }}</span>
        </div>
    </div>

    <div class="glass-panel" style="margin-bottom: 24px;">
        <h3>Project History (Ledger)</h3>
        <div class="table-wrapper" style="margin-top: 16px;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Credit (In)</th>
                        <th>Debit (Out)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = 0; @endphp
                    @forelse($ledger as $item)
                        @if($item['type'] == 'Fund Disbursed')
                            @php $runningBalance += $item['debit']; @endphp {{-- From company debit to manager credit --}}
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M, Y') }}</td>
                                <td style="font-family: monospace;">
                                    <a href="{{ route('shared.funds.invoice', $item['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                        {{ $item['id'] }}
                                    </a>
                                </td>
                                <td><span class="badge" style="background: rgba(0, 230, 118, 0.1); color: var(--success);">Credit</span></td>
                                <td style="font-size: 13px;">{{ $item['description'] }}</td>
                                <td style="text-align: right; color: var(--success); font-weight: 600;">Tk. {{ number_format($item['debit'], 2) }}</td>
                                <td>-</td>
                            </tr>
                        @elseif(str_contains($item['type'], 'Expense'))
                            @php $runningBalance -= $item['debit']; @endphp
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M, Y') }}</td>
                                <td style="font-family: monospace;">
                                    <a href="{{ route('shared.expenses.invoice', $item['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                        {{ $item['id'] }}
                                    </a>
                                </td>
                                <td><span class="badge" style="background: rgba(255, 76, 76, 0.1); color: var(--danger);">Expense</span></td>
                                <td style="font-size: 13px;">
                                    {{ $item['description'] }}
                                </td>
                                <td>-</td>
                                <td style="text-align: right; color: var(--danger); font-weight: 600;">Tk. {{ number_format($item['debit'], 2) }}</td>
                            </tr>
                        @elseif($item['type'] == 'Fund Returned by PM')
                            @php $runningBalance -= $item['credit']; @endphp {{-- Money out of manager hand --}}
                            <tr>
                                <td>{{ $item['date'] }}</td>
                                <td style="font-family: monospace;">
                                    <a href="{{ route('shared.returns.invoice', $item['original_id']) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                        {{ $item['id'] }}
                                    </a>
                                </td>
                                <td><span class="badge badge-completed">Fund Returned</span></td>
                                <td>{{ $item['description'] }}</td>
                                <td>-</td>
                                <td style="color: var(--danger);">Tk. {{ number_format($item['credit'], 2) }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary);">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($ledger) > 0)
                    <tfoot>
                        <tr style="background: rgba(255,255,255,0.05); font-weight: bold;">
                            <td colspan="4" style="text-align: right;">Totals:</td>
                            <td style="color: var(--success);">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</td>
                            <td style="color: var(--danger);">Tk. {{ number_format($summary['total_expenses'] + ($summary['total_manager_returns'] ?? 0), 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
