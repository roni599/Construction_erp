@extends('layouts.app')

@section('title', 'My Reports')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <style>
        /* Local overrides if any */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <h2>My Financial Reports</h2>
    </div>

    <!-- Filter Section -->
    <div class="glass-panel no-print" style="margin-bottom: 32px;">
        <form action="{{ route('manager.reports.generate') }}" method="GET">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="form-group">
                    <label class="form-label">Select Project</label>
                    <select name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Choose Project --</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ (isset($filters['project_id']) && $filters['project_id'] == $p->id) ? 'selected' : '' }}>
                                {{ $p->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="all" {{ (isset($filters['report_type']) && $filters['report_type'] == 'all') ? 'selected' : '' }}>All Transactions</option>
                        <option value="fund_pm" {{ (isset($filters['report_type']) && $filters['report_type'] == 'fund_pm') ? 'selected' : '' }}>Funds Received</option>
                        <option value="expense" {{ (isset($filters['report_type']) && $filters['report_type'] == 'expense') ? 'selected' : '' }}>My Expenses</option>
                        <option value="fund_return" {{ (isset($filters['report_type']) && $filters['report_type'] == 'fund_return') ? 'selected' : '' }}>Fund Return</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                </div>
            </div>
            <div style="margin-top: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('manager.reports.index') }}" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">Clear</a>
                </div>
                @if(isset($report_data))
                <div style="display: flex; gap: 10px;">
                    <button type="button" onclick="openPrintView()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" onclick="exportToPDF()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                    </button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                    </button>
                </div>
                @endif
            </div>
        </form>
    </div>

    @if(isset($report_data))
        <div id="report-container">
            <div class="glass-panel" style="margin-bottom: 24px; text-align: center;">
                <h2 style="margin-bottom: 8px;">My Project Report</h2>
                <h3 style="color: var(--accent-blue); margin-bottom: 12px;">{{ $selected_project->project_name }}</h3>
                @if(!empty($filters['from_date']) || !empty($filters['to_date']))
                    <p style="color: var(--text-secondary);">
                        Report Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}
                    </p>
                @endif
            </div>

            <div class="glass-panel">
                <div class="table-wrapper">
                    <table class="table" id="report-table">
                        <thead>
                            @php
                                $type = $filters['report_type'] ?? 'all';
                                $showIn = in_array($type, ['all', 'fund_pm', 'fund_return', 'client_received']);
                                $showOut = in_array($type, ['all', 'expense']);
                                $colCount = 5 + ($showIn ? 1 : 0) + ($showOut ? 1 : 0);
                            @endphp
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th>Category</th>
                                <th>Description</th>
                                @if($showIn) <th style="text-align: right;">Received (In)</th> @endif
                                @if($showOut) <th style="text-align: right;">Spent (Out)</th> @endif
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $totalIn = 0; 
                                $totalOut = 0; 
                            @endphp
                            @forelse($report_data as $item)
                                @php 
                                    $totalIn += $item['credit']; 
                                    $totalOut += $item['debit']; 
                                @endphp
                                <tr>
                                    <td>{{ $item['date']->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="badge" style="background: {{ $item['credit'] > 0 ? 'rgba(0, 230, 118, 0.1)' : 'rgba(255, 82, 82, 0.1)' }}; color: {{ $item['credit'] > 0 ? 'var(--success)' : 'var(--danger)' }};">
                                            {{ $item['type'] }}
                                        </span>
                                    </td>
                                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $item['method']) }}</td>
                                    <td>{{ $item['category'] }}</td>
                                    <td>{{ $item['description'] }}</td>
                                    @if($showIn)
                                    <td style="text-align: right; color: var(--success);">
                                        {{ $item['credit'] > 0 ? 'Tk. ' . number_format($item['credit'], 2) : '-' }}
                                    </td>
                                    @endif
                                    @if($showOut)
                                    <td style="text-align: right; color: var(--danger);">
                                        {{ $item['debit'] > 0 ? 'Tk. ' . number_format($item['debit'], 2) : '-' }}
                                    </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colCount }}" style="text-align: center; padding: 40px; color: var(--text-secondary);">No records found for the selected criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($report_data->count() > 0)
                            <tfoot style="background: rgba(255,255,255,0.05);">
                                <tr>
                                    <th colspan="5" style="text-align: right;">Total:</th>
                                    @if($showIn) <th style="text-align: right; color: var(--success);">Tk. {{ number_format($totalIn, 2) }}</th> @endif
                                    @if($showOut) <th style="text-align: right; color: var(--danger);">Tk. {{ number_format($totalOut, 2) }}</th> @endif
                                </tr>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if(isset($report_data))
    <script>
        function exportToExcel() {
            const downloadDate = new Date().toLocaleDateString();
            @php
                $typeLabels = [
                    'all' => 'All Transactions',
                    'fund_pm' => 'Funds Received',
                    'expense' => 'My Expenses',
                    'fund_return' => 'Fund Returns'
                ];
                $currentType = $typeLabels[$filters['report_type'] ?? 'all'] ?? 'Financial Report';
            @endphp
            const projectTitle = "Project {{ $currentType }}";
            const projectName = "Project: {{ $selected_project->project_name ?? '' }}";
            const reportPeriod = "@if(!empty($filters['from_date']) || !empty($filters['to_date']))Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}@endif";

            const headerStyle = {
                alignment: { horizontal: "center" },
                font: { bold: true, sz: 14 }
            };
            const subHeaderStyle = {
                alignment: { horizontal: "center" },
                font: { bold: true }
            };

            const headerData = [
                [{ v: projectTitle, s: headerStyle }],
                [{ v: projectName, s: subHeaderStyle }]
            ];
            
            if (reportPeriod) {
                headerData.push([{ v: reportPeriod, s: { alignment: { horizontal: "center" } } }]);
            }
            headerData.push([{ v: "Date: " + downloadDate, s: { alignment: { horizontal: "center" } } }]);
            headerData.push([]); // Spacer row

            const ws = XLSX.utils.aoa_to_sheet(headerData);
            
            // 1. Header Merges
            const colCount = {{ $colCount }};
            const merges = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } }
            ];
            let currentRow = 2;
            if (reportPeriod) {
                merges.push({ s: { r: currentRow, c: 0 }, e: { r: currentRow, c: colCount - 1 } });
                currentRow++;
            }
            merges.push({ s: { r: currentRow, c: 0 }, e: { r: currentRow, c: colCount - 1 } });
            
            // 2. Add Table
            const table = document.getElementById("report-table");
            const tableClone = table.cloneNode(true);
            const tfoot = tableClone.querySelector('tfoot');
            if (tfoot) tfoot.remove();
            
            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            // 3. Footer Data & Merges
            const footerData = [];
            
            // Total Row
            const totalRow = ['Total:', '', '', '', ''];
            @if($showIn) totalRow.push("Tk. {{ number_format($totalIn ?? 0, 2) }}"); @endif
            @if($showOut) totalRow.push("Tk. {{ number_format($totalOut ?? 0, 2) }}"); @endif
            footerData.push(totalRow);

            
            const startFooterRow = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
            XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });

            merges.push({ s: { r: startFooterRow, c: 0 }, e: { r: startFooterRow, c: 4 } });

            // Style footer
            const row = startFooterRow;
            // Label styling
            const labelRef = XLSX.utils.encode_cell({ r: row, c: 0 });
            if (ws[labelRef]) ws[labelRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
            
            // Value styling (Col 5 and 6)
            [5, 6].forEach(col => {
                const cellRef = XLSX.utils.encode_cell({ r: row, c: col });
                let align = "left";
                if (col === 6) align = "right"; // Total Out stays right
                if (col === 5) align = "left"; // Total In left
                if (ws[cellRef]) ws[cellRef].s = { alignment: { horizontal: align }, font: { bold: true } };
            });

            ws['!merges'] = merges;

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "My Project Report");
            XLSX.writeFile(wb, "My_Project_Report_{{ $selected_project->project_name ?? 'Export' }}.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            const downloadDate = new Date().toLocaleDateString();
            @php
                $typeLabels = [
                    'all' => 'All Transactions',
                    'fund_pm' => 'Funds Received',
                    'expense' => 'My Expenses',
                    'fund_return' => 'Fund Returns'
                ];
                $currentType = $typeLabels[$filters['report_type'] ?? 'all'] ?? 'Financial Report';
            @endphp
            const projectTitle = "Project {{ $currentType }}";
            const projectName = "Project: {{ $selected_project->project_name ?? '' }}";
            const reportPeriod = "@if(!empty($filters['from_date']) || !empty($filters['to_date']))Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}@endif";

            const colCount = {{ $colCount }};
            const showIn = {{ $showIn ? 'true' : 'false' }};
            const showOut = {{ $showOut ? 'true' : 'false' }};

            // 1. Manually extract body data
            const body = [];
            const tableBodyRows = document.querySelectorAll('#report-table tbody tr');
            tableBodyRows.forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => {
                    rowData.push(cell.innerText.trim());
                });
                if (rowData.length > 0) body.push(rowData);
            });

            // 2. Setup values
            const totalIn = "{{ number_format($totalIn ?? 0, 2) }}";
            const totalOut = "{{ number_format($totalOut ?? 0, 2) }}";

            // 3. Draw Bordered Header Table
            const headerRows = [
                [{ content: projectTitle.toUpperCase(), styles: { halign: 'center', fontSize: 14, fontStyle: 'bold', fillColor: [248, 249, 250] } }],
                [{ content: projectName, styles: { halign: 'center', fontSize: 10, fontStyle: 'bold' } }]
            ];
            
            if (reportPeriod) {
                headerRows.push([{ content: reportPeriod, styles: { halign: 'center', fontSize: 9 } }]);
            }
            headerRows.push([{ content: 'Date: ' + downloadDate, styles: { halign: 'center', fontSize: 9 } }]);

            doc.autoTable({
                body: headerRows,
                startY: 10,
                theme: 'grid',
                styles: { cellPadding: 2, lineColor: [0, 0, 0], lineWidth: 0.2, textColor: [0, 0, 0] },
                margin: { left: 14, right: 14 }
            });

            let nextY = doc.lastAutoTable.finalY;

            const tableHeaders = [
                { content: 'Date', styles: { halign: 'center' } },
                { content: 'Type', styles: { halign: 'center' } },
                { content: 'Method', styles: { halign: 'center' } },
                { content: 'Category', styles: { halign: 'center' } },
                { content: 'Description', styles: { halign: 'center' } }
            ];
            if(showIn) tableHeaders.push({ content: 'Received (In)', styles: { halign: 'right' } });
            if(showOut) tableHeaders.push({ content: 'Spent (Out)', styles: { halign: 'right' } });
            
            let headRows = [tableHeaders];

            // 4. Construct Footer
            const foot = [];
            const footTotal = [{ content: 'Total:', colSpan: 5, styles: { halign: 'right' } }];
            if(showIn) footTotal.push({ content: 'Tk. ' + totalIn, styles: { halign: 'right' } });
            if(showOut) footTotal.push({ content: 'Tk. ' + totalOut, styles: { halign: 'right' } });
            foot.push(footTotal);


            // 5. Generate the table
            const columnStyles = {};
            if (showIn && showOut) {
                columnStyles[5] = { halign: 'right' };
                columnStyles[6] = { halign: 'right' };
            } else if (showIn || showOut) {
                columnStyles[5] = { halign: 'right' };
            }

            doc.autoTable({
                head: headRows,
                body: body,
                foot: foot,
                startY: nextY, // No gap
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1, lineColor: [200, 200, 200] },
                headStyles: { fillColor: [41, 128, 185], textColor: 255, halign: 'center' },
                columnStyles: columnStyles,
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' }
            });

            doc.save("My_Project_Report_{{ $selected_project->project_name ?? 'Export' }}.pdf");
        }

        function openPrintView() {
            const params = new URLSearchParams(window.location.search);
            const url = "{{ route('manager.reports.print') }}?" + params.toString();
            window.open(url, '_blank');
        }
    </script>
    @endif
@endsection
