@extends('layouts.app')

@section('title', 'Project Ledger: ' . $project->project_name)

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
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

    <div class="glass-panel" style="margin-bottom: 24px; padding: 16px;">
        <form method="GET" action="{{ route('manager.projects.ledger', $project->id) }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
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
                <input type="text" name="invoice_no" class="form-control" placeholder="Search Txn ID..." value="{{ request('invoice_no') }}">
            </div>
            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-search"></i> Search</button>
                <a href="{{ route('manager.projects.ledger', $project->id) }}" class="btn btn-outline" style="white-space: nowrap;">Clear</a>
            </div>
            <div style="display: flex; gap: 10px; margin-left: auto;">
                <button type="button" onclick="window.print()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-print"></i> Print
                </button>
                <button type="button" onclick="exportLedgerToPDF()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                </button>
                <button type="button" onclick="exportLedgerToExcel()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                </button>
            </div>
        </form>
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
    <script>
        function exportLedgerToExcel() {
            const table = document.querySelector('.table');
            const ws = XLSX.utils.table_to_sheet(table);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Project_Ledger");
            XLSX.writeFile(wb, "Project_Ledger_{{ $project->project_name }}.xlsx");
        }

        function exportLedgerToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            doc.text("Project Ledger: {{ $project->project_name }}", 14, 15);
            doc.setFontSize(10);
            doc.text("Client: {{ $project->client_name }}", 14, 22);
            doc.text("Date: " + new Date().toLocaleDateString(), 14, 28);

            doc.autoTable({
                html: '.table',
                startY: 35,
                theme: 'grid',
                styles: { fontSize: 8 },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 }
            });

            doc.save("Project_Ledger_{{ $project->project_name }}.pdf");
        }
    </script>
@endsection
