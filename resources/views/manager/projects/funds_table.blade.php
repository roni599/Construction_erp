<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Project Name</th>
            <th>Method</th>
            <th>Given By</th>
            <th style="text-align: right;">Amount (Tk.)</th>
            <th style="text-align: center;">Action</th>
        </tr>
    </thead>
    <tbody>
        @php $totalAmount = 0; @endphp
        @forelse($funds as $fund)
            @php $totalAmount += $fund->amount; @endphp
            <tr>
                <td>{{ $fund->fund_date->format('Y-m-d') }}</td>
                <td style="font-family: monospace;">
                    <a href="{{ route('shared.funds.invoice', $fund->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        {{ $fund->invoice_no ?? 'N/A' }}
                    </a>
                </td>
                <td>
                    <a href="{{ route('manager.projects.show', $fund->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                        <strong>{{ $fund->project->project_name }}</strong>
                    </a>
                </td>
                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fund->payment_method) }}</td>
                <td>{{ $fund->givenBy->name ?? 'Admin' }}</td>
                <td style="text-align: right; color: var(--accent-blue); font-weight: bold;">+{{ number_format($fund->amount, 2) }}</td>
                <td>
                    <div class="dropdown" style="text-align: center;">
                        <button class="dropdown-toggle" type="button">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('manager.projects.ledger', $fund->project_id) }}">
                                <i class="fas fa-file-invoice"></i> Ledger
                            </a>
                            <a class="dropdown-item" href="{{ route('manager.projects.show', $fund->project_id) }}">
                                <i class="fas fa-plus-circle"></i> Record Expense
                            </a>
                            <a class="dropdown-item" href="{{ route('shared.funds.invoice', $fund->id) }}" target="_blank">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                    No received funds found for the selected filters.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($funds) > 0)
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right; font-size: 16px;">Total:</th>
                <th style="text-align: right; color: var(--accent-blue); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    @endif
</table>

<div class="custom-pagination" style="margin-top: 24px; padding: 0 16px 16px;">
    {{ $funds->appends(request()->query())->links() }}
</div>
