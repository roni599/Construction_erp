<table class="table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Invoice No</th>
            <th>Project Name</th>
            <th>Category</th>
            <th>Description</th>
            <th style="text-align: right;">Amount (Tk.)</th>
            <th style="text-align: center;">Status</th>
            <th style="text-align: center;">Receipt</th>
            <th style="text-align: center;">Action</th>
        </tr>
    </thead>
    <tbody>
        @php $totalAmount = 0; @endphp
        @forelse($expenses as $expense)
            @php 
                if($expense->status === 'approved') {
                    $totalAmount += $expense->amount;
                }
            @endphp
            <tr>
                <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td style="font-family: monospace;">
                    <a href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                        {{ $expense->invoice_no ?? 'N/A' }}
                    </a>
                </td>
                <td>
                    <a href="{{ route('manager.projects.show', $expense->project_id) }}" style="color: var(--accent-blue); text-decoration: none;">
                        <strong>{{ $expense->project->project_name }}</strong>
                    </a>
                </td>
                <td><span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-primary);">{{ $expense->category->name ?? 'N/A' }}</span></td>
                <td>{{ $expense->description ?? '-' }}</td>
                <td style="text-align: right; color: var(--danger); font-weight: bold; {{ ($expense->status !== 'approved') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                    -Tk. {{ number_format($expense->amount, 2) }}
                </td>
                <td style="text-align: center;">
                    <span class="badge" style="
                        @if($expense->status === 'approved')
                            background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);
                        @elseif($expense->status === 'rejected')
                            background: rgba(220, 53, 69, 0.1); color: var(--danger); border: 1px solid var(--danger);
                        @else
                            background: rgba(255, 193, 7, 0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);
                        @endif
                    ">
                        {{ ucfirst($expense->status ?? 'pending') }}
                    </span>
                </td>
                <td style="text-align: center;">
                    @if($expense->bill_image)
                        <a href="{{ $expense->bill_image }}" target="_blank" class="btn btn-outline" style="padding: 4px 8px; font-size: 11px;">
                            <i class="fas fa-image"></i> View
                        </a>
                    @else
                        <span style="color: var(--text-secondary); font-size: 11px;">No Receipt</span>
                    @endif
                </td>
                <td>
                    <div class="dropdown" style="text-align: center;">
                        <button class="dropdown-toggle" type="button">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('manager.projects.ledger', $expense->project_id) }}">
                                <i class="fas fa-file-invoice"></i> Ledger
                            </a>
                            <a class="dropdown-item" href="{{ route('manager.projects.show', $expense->project_id) }}">
                                <i class="fas fa-plus-circle"></i> Record Expense
                            </a>
                            <a class="dropdown-item" href="{{ route('shared.expenses.invoice', $expense->id) }}" target="_blank">
                                <i class="fas fa-file-invoice"></i> Invoice
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 24px;">
                    No expenses found for the selected filters.
                </td>
            </tr>
        @endforelse
    </tbody>
    @if(count($expenses) > 0)
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right; font-size: 16px;">Approved Total:</th>
                <th style="text-align: right; color: var(--danger); font-size: 16px;">Tk. {{ number_format($totalAmount, 2) }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    @endif
</table>

<div class="custom-pagination" style="margin-top: 24px; padding: 0 16px 16px;">
    {{ $expenses->appends(request()->query())->links() }}
</div>
