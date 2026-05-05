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
        <form id="ledger-filter-form" method="GET" action="{{ route('manager.projects.ledger', $project->id) }}" style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;">
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
                <button type="button" onclick="printLedgerBlade()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
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
                        <th style="text-align: center;">Status</th>
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
                                <td style="font-size: 13px;">{{ $item['description'] ?: '-' }}</td>
                                <td style="text-align: center;"><span class="badge" style="background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);">Approved</span></td>
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
                                    {{ $item['description'] ?: '-' }}
                                </td>
                                <td style="text-align: center;">
                                    @if(isset($item['status']))
                                        <span class="badge" style="
                                            padding: 2px 8px; font-size: 10px;
                                            @if($item['status'] === 'approved')
                                                background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);
                                            @elseif($item['status'] === 'rejected')
                                                background: rgba(220, 53, 69, 0.1); color: var(--danger); border: 1px solid var(--danger);
                                            @else
                                                background: rgba(255, 193, 7, 0.1); color: var(--accent-yellow); border: 1px solid var(--accent-yellow);
                                            @endif
                                        ">
                                            {{ ucfirst($item['status']) }}
                                        </span>
                                    @else
                                        <span class="badge" style="background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);">Approved</span>
                                    @endif
                                </td>
                                <td>-</td>
                                <td style="text-align: right; color: var(--danger); font-weight: 600; {{ (isset($item['status']) && $item['status'] !== 'approved') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                    Tk. {{ number_format($item['amount'], 2) }}
                                </td>
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
                                <td>{{ $item['description'] ?: '-' }}</td>
                                <td style="text-align: center;"><span class="badge" style="background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);">Approved</span></td>
                                <td>-</td>
                                <td style="color: var(--danger); text-align: right;">Tk. {{ number_format($item['credit'], 2) }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--text-secondary);">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($ledger) > 0)
                    <tfoot>
                        <tr class="footer-row" style="background: rgba(255,255,255,0.05); font-weight: bold;">
                            <td colspan="6" style="text-align: right; padding-right: 15px;">Total Funds Disbursed to PM:</td>
                            <td style="color: var(--success); text-align: right; padding-right: 10px;">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</td>
                        </tr>
                        <tr class="footer-row" style="background: rgba(255,255,255,0.05); font-weight: bold;">
                            <td colspan="6" style="text-align: right; padding-right: 15px;">Total Debit (Expenses):</td>
                            <td style="color: var(--danger); text-align: right; padding-right: 10px;">Tk. {{ number_format($summary['pm_expenses'], 2) }}</td>
                        </tr>
                        <tr class="footer-row" style="background: rgba(255,255,255,0.05); font-weight: bold;">
                            <td colspan="6" style="text-align: right; padding-right: 15px;">Total Fund Returned:</td>
                            <td style="color: var(--accent-yellow); text-align: right; padding-right: 10px;">Tk. {{ number_format($summary['total_manager_returns'], 2) }}</td>
                        </tr>
                        <tr class="footer-row" style="background: rgba(255,255,255,0.1); font-weight: bold;">
                            <td colspan="6" style="text-align: right; padding-right: 15px;">Manager Hand Cash (Funds - Expenses):</td>
                            <td style="color: var(--success); text-align: right; padding-right: 10px;">Tk. {{ number_format($summary['total_manager_funds'] - $summary['pm_expenses'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
    <script>
        function printLedgerBlade() {
            const url = "{{ route('manager.projects.ledger.print', $project->id) }}?" + new URLSearchParams(new FormData(document.getElementById('ledger-filter-form'))).toString();
            window.open(url, '_blank');
        }

        function exportLedgerToExcel() {
            const projectTitle = "PROJECT HISTORY (LEDGER)";
            const projectName = "Project: {{ $project->project_name }}";
            const downloadDate = new Date().toLocaleDateString();

            // 1. Create Header AOA
            const headerData = [
                [projectTitle],
                [projectName],
                ['Date: ' + downloadDate]
            ];

            const ws = XLSX.utils.aoa_to_sheet(headerData);

            // 2. Add Table Data
            const table = document.querySelector('.table');
            const tableClone = table.cloneNode(true);
            // Remove links from table for clean excel
            tableClone.querySelectorAll('a').forEach(a => a.replaceWith(a.innerText));
            
            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            // 3. Apply Styles & Merges
            const colCount = 7;
            const merges = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } },
                { s: { r: 2, c: 0 }, e: { r: 2, c: colCount - 1 } }
            ];
            ws['!merges'] = merges;

            // Header styles
            ws['A1'].s = { alignment: { horizontal: "center" }, font: { bold: true, sz: 14 } };
            ws['A2'].s = { alignment: { horizontal: "center" }, font: { bold: true, sz: 10 } };
            ws['A3'].s = { alignment: { horizontal: "center" }, font: { sz: 9 } };

            // 4. Handle Rejected Strike-through
            const range = XLSX.utils.decode_range(ws['!ref']);
            const tableHeaderRow = 3; // headerData.length
            const footerRowsCount = 4;
            
            for (let R = tableHeaderRow + 1; R <= range.e.r - footerRowsCount; R++) {
                const statusCellRef = XLSX.utils.encode_cell({ r: R, c: 4 }); // Status Col
                const statusVal = ws[statusCellRef] ? String(ws[statusCellRef].v).trim().toLowerCase() : '';
                
                if (statusVal === 'rejected') {
                    const colsToCheck = [5, 6]; // Credit & Debit
                    colsToCheck.forEach(C => {
                        const cellRef = XLSX.utils.encode_cell({ r: R, c: C });
                        if (ws[cellRef]) {
                            ws[cellRef].s = ws[cellRef].s || {};
                            ws[cellRef].s.font = ws[cellRef].s.font || {};
                            ws[cellRef].s.font.strike = true;
                            ws[cellRef].s.font.color = { rgb: "999999" };
                        }
                    });
                }
            }

            // 5. Handle Footer Alignment & Merging in Excel
            const lastRow = range.e.r;
            for (let i = 0; i < footerRowsCount; i++) {
                const row = lastRow - i;
                merges.push({ s: { r: row, c: 0 }, e: { r: row, c: 5 } });
                const labelRef = XLSX.utils.encode_cell({ r: row, c: 0 });
                const valRef = XLSX.utils.encode_cell({ r: row, c: 6 });
                if (ws[labelRef]) ws[labelRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
                if (ws[valRef]) ws[valRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
            }

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Project Ledger");
            XLSX.writeFile(wb, "Project_Ledger_{{ $project->project_name }}.xlsx");
        }

        function exportLedgerToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            const projectTitle = "PROJECT HISTORY (LEDGER)";
            const projectName = "Project: {{ $project->project_name }}";
            const downloadDate = new Date().toLocaleDateString();

            const headerRows = [
                [{ content: projectTitle, styles: { halign: 'center', fontSize: 14, fontStyle: 'bold' } }],
                [{ content: projectName, styles: { halign: 'center', fontSize: 10, fontStyle: 'bold' } }],
                [{ content: 'Date: ' + downloadDate, styles: { halign: 'center', fontSize: 9 } }]
            ];

            doc.autoTable({
                body: headerRows,
                startY: 10,
                theme: 'grid',
                styles: { cellPadding: 2, lineColor: [0, 0, 0], lineWidth: 0.1, textColor: [0, 0, 0] },
                margin: { left: 14, right: 14 }
            });

            let nextY = doc.lastAutoTable.finalY; // Removed the 10mm gap

            doc.autoTable({
                html: '.table',
                startY: nextY,
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1 },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                columnStyles: {
                    5: { halign: 'right' },
                    6: { halign: 'right' }
                },
                foot: [
                    [{ content: 'Total Funds Disbursed to PM:', colSpan: 6, styles: { halign: 'right', fontStyle: 'bold' } }, { content: 'Tk. {{ number_format($summary['total_manager_funds'], 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }],
                    [{ content: 'Total Debit (Expenses):', colSpan: 6, styles: { halign: 'right', fontStyle: 'bold' } }, { content: 'Tk. {{ number_format($summary['pm_expenses'], 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }],
                    [{ content: 'Total Fund Returned:', colSpan: 6, styles: { halign: 'right', fontStyle: 'bold' } }, { content: 'Tk. {{ number_format($summary['total_manager_returns'], 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }],
                    [{ content: 'Manager Hand Cash (Funds - Expenses):', colSpan: 6, styles: { halign: 'right', fontStyle: 'bold', fillColor: [240, 240, 240] } }, { content: 'Tk. {{ number_format($summary['total_manager_funds'] - $summary['pm_expenses'], 2) }}', styles: { halign: 'right', fontStyle: 'bold', fillColor: [240, 240, 240] } }]
                ],
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], lineWidth: 0.1, halign: 'right' },
                didDrawCell: function (data) {
                    // Status is 4, Credit is 5, Debit is 6
                    const isAmountCol = (data.column.index === 5 || data.column.index === 6);
                    if (data.section === 'body' && isAmountCol) {
                        const statusText = (data.row.cells[4].text[0] || '').trim().toLowerCase();
                        
                        if (statusText === 'rejected') {
                            const { cell } = data;
                            const lineX = cell.x + 2;
                            const lineY = cell.y + (cell.height / 2);
                            const lineWidth = cell.width - 4;
                            
                            doc.setDrawColor(150, 150, 150);
                            doc.setLineWidth(0.3);
                            doc.line(lineX, lineY, lineX + lineWidth, lineY);
                        }
                    }
                }
            });

            doc.save("Project_Ledger_{{ $project->project_name }}.pdf");
        }
    </script>
@endsection
