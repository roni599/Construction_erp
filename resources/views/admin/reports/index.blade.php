@extends('layouts.app')

@section('title', 'Project Report')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <style>
        /* Local overrides if any, but removing print styles as they are now global */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
    </style>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <h2>Financial Reports</h2>
    </div>

    <!-- Filter Section -->
    <div class="glass-panel no-print" style="margin-bottom: 32px;">
        <form action="{{ route('admin.reports.generate') }}" method="GET">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 0 !important;">
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
                        <option value="client_received" {{ (isset($filters['report_type']) && $filters['report_type'] == 'client_received') ? 'selected' : '' }}>Client Received</option>
                        <option value="fund_pm" {{ (isset($filters['report_type']) && $filters['report_type'] == 'fund_pm') ? 'selected' : '' }}>Fund Transferred PM</option>
                        <option value="expense" {{ (isset($filters['report_type']) && $filters['report_type'] == 'expense') ? 'selected' : '' }}>Project Expenses</option>
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
                <div class="form-group">
                    <label class="form-label">Invoice No</label>
                    <input type="text" name="invoice_no" class="form-control" placeholder="e.g. PAY-123" value="{{ $filters['invoice_no'] ?? '' }}">
                </div>
            </div>
            <div class="btn-group" style="margin-top: 24px; flex-wrap: wrap; flex-direction: row !important; justify-content: space-between;">
                <!-- Search & Clear Group -->
                <div class="btn-group" style="flex: 1; min-width: 200px; flex-direction: row !important;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline" style="flex: 1; border-color: var(--danger); color: var(--danger);">
                        <i class="fas fa-redo"></i> Clear
                    </a>
                </div>
                
                <!-- Export Buttons Group -->
                <div class="btn-group" style="flex: 1.5; min-width: 300px; justify-content: flex-end; flex-direction: row !important;">
                    @php $hasData = isset($report_data); @endphp
                    <button type="button" onclick="{{ $hasData ? 'openPrintView()' : 'alert(\'Please search and generate a report first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" onclick="{{ $hasData ? 'exportToPDF()' : 'alert(\'Please search and generate a report first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                    </button>
                    <button type="button" onclick="{{ $hasData ? 'exportToExcel()' : 'alert(\'Please search and generate a report first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(isset($report_data))
        <div id="report-container">
            <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 20px; text-align: center;">
                <h2 style="margin-bottom: 8px;">Project Financial Report</h2>
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
                                $showIn = in_array($type, ['all', 'client_received', 'fund_return']);
                                $showOut = in_array($type, ['all', 'fund_pm', 'expense']);
                                $showStatus = in_array($type, ['all', 'expense']);
                                $colCount = 6 + ($showStatus ? 1 : 0) + ($showIn ? 1 : 0) + ($showOut ? 1 : 0);
                            @endphp
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Type</th>
                                <th>Method</th>
                                <th>Category</th>
                                <th>Description</th>
                                @if($showStatus) <th style="text-align: center;">Status</th> @endif
                                @if($showIn) <th style="text-align: center;">Received (In)</th> @endif
                                @if($showOut) <th style="text-align: center;">Spent (Out)</th> @endif
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
                                        <a href="{{ $item['invoice_url'] }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none; font-weight: 600;">
                                            {{ $item['invoice_no'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: {{ $item['credit'] > 0 ? 'rgba(0, 230, 118, 0.1)' : 'rgba(255, 82, 82, 0.1)' }}; color: {{ $item['credit'] > 0 ? 'var(--success)' : 'var(--danger)' }};">
                                            {{ $item['type'] }}
                                        </span>
                                    </td>
                                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $item['method']) }}</td>
                                    <td>{{ $item['category'] }}</td>
                                    <td>
                                        {{ $item['description'] ?: '-' }}
                                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                            <i class="fas fa-user-edit" style="font-size: 10px;"></i> Recorded By: {{ $item['recorded_by'] ?? 'N/A' }}
                                        </div>
                                    </td>
                                    @if($showStatus)
                                    <td style="text-align: center;">
                                        @if(isset($item['status']))
                                            <span class="badge" style="background: {{ $item['status'] === 'approved' ? 'rgba(40, 167, 69, 0.1)' : ($item['status'] === 'rejected' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)') }}; color: {{ $item['status'] === 'approved' ? 'var(--success)' : ($item['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }}; border: 1px solid {{ $item['status'] === 'approved' ? 'var(--success)' : ($item['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }};">
                                                {{ ucfirst($item['status']) }}
                                            </span>
                                        @else
                                            <span class="badge" style="background: rgba(40, 167, 69, 0.1); color: var(--success); border: 1px solid var(--success);">Approved</span>
                                        @endif
                                    </td>
                                    @endif
                                    @if($showIn)
                                    <td style="text-align: right; color: var(--success);">
                                        {{ $item['credit'] > 0 ? 'Tk. ' . number_format($item['credit'], 2) : '-' }}
                                    </td>
                                    @endif
                                    @if($showOut)
                                    <td style="text-align: right; color: var(--danger); {{ ($item['type'] === 'Expense' && isset($item['status']) && $item['status'] !== 'approved') ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                        @if($item['type'] === 'Expense')
                                            Tk. {{ number_format($item['amount'], 2) }}
                                        @else
                                            {{ $item['debit'] > 0 ? 'Tk. ' . number_format($item['debit'], 2) : '-' }}
                                        @endif
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
                            @php
                                $totalClientReceived = $report_data->where('type', 'Client Payment')->sum('credit');
                                $totalTransferredToPM = $report_data->where('type', 'Fund Disbursed')->sum('debit');
                                $totalPMExpenses = $report_data->where('type', 'Expense')->sum('debit');
                                $totalFundReturned = $report_data->where('type', 'Fund Returned')->sum('credit');
                                
                                $officeBalance = ($totalClientReceived + $totalFundReturned) - $totalTransferredToPM;
                                $pmHandCash = $totalTransferredToPM - ($totalPMExpenses + $totalFundReturned);
                            @endphp
                            <tfoot style="background: rgba(255,255,255,0.05);">
                                @if(in_array($type, ['all', 'client_received']))
                                <tr>
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right;">Total Received from Client:</th>
                                    <th colspan="2" style="text-align: right; color: var(--success); font-size: 1.1em; padding-right: 12px;">
                                        Tk. {{ number_format($totalClientReceived, 2) }}
                                    </th>
                                </tr>
                                @endif
                                @if(in_array($type, ['all', 'fund_pm']))
                                <tr>
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right;">Total Transferred to PM:</th>
                                    <th colspan="2" style="text-align: right; color: var(--accent-blue); font-size: 1.1em; padding-right: 12px;">
                                        Tk. {{ number_format($totalTransferredToPM, 2) }}
                                    </th>
                                </tr>
                                @endif
                                @if(in_array($type, ['all', 'expense']))
                                <tr>
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right;">Total PM Expenses:</th>
                                    <th colspan="2" style="text-align: right; color: var(--danger); font-size: 1.1em; padding-right: 12px;">
                                        Tk. {{ number_format($totalPMExpenses, 2) }}
                                    </th>
                                </tr>
                                @endif
                                @if(in_array($type, ['all', 'fund_return']))
                                <tr>
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right;">Total Fund Returned by PM:</th>
                                    <th colspan="2" style="text-align: right; color: var(--accent-yellow); font-size: 1.1em; padding-right: 12px;">
                                        Tk. {{ number_format($totalFundReturned, 2) }}
                                    </th>
                                </tr>
                                @endif
                                @if($type === 'all')
                                <tr style="border-top: 2px solid var(--border-color);">
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right; font-weight: bold;">Office Balance (Received - Transferred):</th>
                                    <th colspan="2" style="text-align: right; color: {{ $officeBalance >= 0 ? 'var(--success)' : 'var(--danger)' }}; font-weight: bold; font-size: 1.2em; padding-right: 12px;">
                                        Tk. {{ number_format($officeBalance, 2) }}
                                    </th>
                                </tr>
                                <tr style="border-top: 1px dashed var(--border-color);">
                                    <th colspan="{{ 6 + ($showStatus ? 1 : 0) }}" style="text-align: right;">PM Hand Cash (Transferred - Expenses):</th>
                                    <th colspan="2" style="text-align: right; color: {{ $pmHandCash >= 0 ? 'var(--accent-blue)' : 'var(--danger)' }}; font-size: 1.1em; padding-right: 12px;">
                                        Tk. {{ number_format($pmHandCash, 2) }}
                                    </th>
                                </tr>
                                @endif
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
            const showIn = {{ $showIn ? 'true' : 'false' }};
            const showOut = {{ $showOut ? 'true' : 'false' }};
            const showStatus = {{ $showStatus ? 'true' : 'false' }};
            @php
                $typeLabels = [
                    'all' => 'All Transactions',
                    'client_received' => 'Client Payments',
                    'fund_pm' => 'Fund Transfers to PM',
                    'expense' => 'Project Expenses',
                    'fund_return' => 'Funds Returned'
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
            
            // 2. Add Table Data
            const tableData = [];
            const tableRows = document.querySelectorAll('#report-table tr');
            tableRows.forEach(row => {
                if (row.parentElement.tagName.toLowerCase() !== 'tfoot') {
                    const rowData = [];
                    row.querySelectorAll('th, td').forEach(cell => {
                        rowData.push(cell.innerText.trim());
                    });
                    if (rowData.length > 0) tableData.push(rowData);
                }
            });
            
            XLSX.utils.sheet_add_aoa(ws, tableData, { origin: -1 });

            // 3. Footer Data
            const footerData = [];
            
            const addFootRow = (label, value) => {
                const row = [label];
                for(let i=0; i < colCount - 2; i++) row.push('');
                row.push(value);
                footerData.push(row);
            };

            @if(in_array($type, ['all', 'client_received']))
            addFootRow('Total Received from Client:', "Tk. {{ number_format($totalClientReceived ?? 0, 2) }}");
            @endif
            @if(in_array($type, ['all', 'fund_pm']))
            addFootRow('Total Transferred to PM:', "Tk. {{ number_format($totalTransferredToPM ?? 0, 2) }}");
            @endif
            @if(in_array($type, ['all', 'expense']))
            addFootRow('Total PM Expenses:', "Tk. {{ number_format($totalPMExpenses ?? 0, 2) }}");
            @endif
            @if(in_array($type, ['all', 'fund_return']))
            addFootRow('Total Fund Returned by PM:', "Tk. {{ number_format($totalFundReturned ?? 0, 2) }}");
            @endif
            @if($type === 'all')
            addFootRow('Office Balance (Received - Transferred):', "Tk. {{ number_format($officeBalance ?? 0, 2) }}");
            addFootRow('PM Hand Cash (Transferred - Expenses):', "Tk. {{ number_format($pmHandCash ?? 0, 2) }}");
            @endif
            
            const startFooterRow = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
            XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });

            const totalLabelCols = colCount - 1;
            for (let i = 0; i < footerData.length; i++) {
                const row = startFooterRow + i;
                merges.push({ s: { r: row, c: 0 }, e: { r: row, c: totalLabelCols - 1 } });
                const cellRef = XLSX.utils.encode_cell({ r: row, c: totalLabelCols });
                if (ws[cellRef]) {
                    ws[cellRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
                }
                const labelRef = XLSX.utils.encode_cell({ r: row, c: 0 });
                if (ws[labelRef]) {
                    ws[labelRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
                }
            }

            // 4. Handle Strike-through for Rejected items in Body
            @if($showStatus)
            const bodyRange = XLSX.utils.decode_range(ws['!ref']);
            const totalExcelRows = bodyRange.e.r + 1;
            const dataRowsOnly = Array.from(tableRows).filter(r => r.parentElement.tagName.toLowerCase() === 'tbody');
            const tableDataStartRow = startFooterRow - dataRowsOnly.length;
            
            for(let i = 0; i < dataRowsOnly.length; i++) {
                const R = tableDataStartRow + i;
                const statusCell = dataRowsOnly[i].cells[6]; // Status index 6
                const statusVal = statusCell ? statusCell.innerText.trim().toLowerCase() : '';
                
                if(statusVal === 'rejected') {
                    const colsToCheck = [7, 8]; 
                    colsToCheck.forEach(C => {
                        const cellRef = XLSX.utils.encode_cell({ r: R, c: C });
                        if(ws[cellRef]) {
                            ws[cellRef].s = ws[cellRef].s || {};
                            ws[cellRef].s.font = ws[cellRef].s.font || {};
                            ws[cellRef].s.font.strike = true;
                            ws[cellRef].s.font.color = { rgb: "999999" };
                        }
                    });
                }
            }
            @endif

            ws['!merges'] = merges;

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Project Report");
            XLSX.writeFile(wb, "Project_Report_{{ $selected_project->project_name ?? 'Export' }}.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('l', 'mm', 'a4');
            
            const downloadDate = new Date().toLocaleDateString();
            @php
                $typeLabels = [
                    'all' => 'All Transactions',
                    'client_received' => 'Client Payments',
                    'fund_pm' => 'Fund Transfers to PM',
                    'expense' => 'Project Expenses',
                    'fund_return' => 'Funds Returned'
                ];
                $currentType = $typeLabels[$filters['report_type'] ?? 'all'] ?? 'Financial Report';
            @endphp
            const projectTitle = "Project {{ $currentType }}";
            const projectName = "Project: {{ $selected_project->project_name ?? '' }}";
            const reportPeriod = "@if(!empty($filters['from_date']) || !empty($filters['to_date']))Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}@endif";

            const colCount = {{ $colCount }};
            const showIn = {{ $showIn ? 'true' : 'false' }};
            const showOut = {{ $showOut ? 'true' : 'false' }};
            const showStatus = {{ $showStatus ? 'true' : 'false' }};

            const body = [];
            document.querySelectorAll('#report-table tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => rowData.push(cell.innerText.trim()));
                if (rowData.length > 0) body.push(rowData);
            });

            const headerRows = [
                [{ content: projectTitle.toUpperCase(), styles: { halign: 'center', fontSize: 14, fontStyle: 'bold' } }],
                [{ content: projectName, styles: { halign: 'center', fontSize: 10, fontStyle: 'bold' } }]
            ];
            if (reportPeriod) headerRows.push([{ content: reportPeriod, styles: { halign: 'center', fontSize: 9 } }]);
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
                { content: 'Invoice', styles: { halign: 'center' } },
                { content: 'Type', styles: { halign: 'center' } },
                { content: 'Method', styles: { halign: 'center' } },
                { content: 'Category', styles: { halign: 'center' } },
                { content: 'Description', styles: { halign: 'center' } }
            ];
            if(showStatus) tableHeaders.push({ content: 'Status', styles: { halign: 'center' } });
            if(showIn) tableHeaders.push({ content: 'Received (In)', styles: { halign: 'right' } });
            if(showOut) tableHeaders.push({ content: 'Spent (Out)', styles: { halign: 'right' } });

            const foot = [];
            @if(in_array($type, ['all', 'client_received']))
            foot.push([{ content: 'Total Received from Client:', colSpan: colCount - 1, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($totalClientReceived ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            @endif
            @if(in_array($type, ['all', 'fund_pm']))
            foot.push([{ content: 'Total Transferred to PM:', colSpan: colCount - 1, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($totalTransferredToPM ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            @endif
            @if(in_array($type, ['all', 'expense']))
            foot.push([{ content: 'Total PM Expenses:', colSpan: colCount - 1, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($totalPMExpenses ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            @endif
            @if(in_array($type, ['all', 'fund_return']))
            foot.push([{ content: 'Total Fund Returned by PM:', colSpan: colCount - 1, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($totalFundReturned ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            @endif
            @if($type === 'all')
            foot.push([{ content: 'Office Balance (Received - Transferred):', colSpan: colCount - 1, styles: { halign: 'right', fontStyle: 'bold' } }, { content: 'Tk. {{ number_format($officeBalance ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            foot.push([{ content: 'PM Hand Cash (Transferred - Expenses):', colSpan: colCount - 1, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($pmHandCash ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]);
            @endif

            const columnStyles = {};
            const amountStartCol = 6 + (showStatus ? 1 : 0);
            if (showIn && showOut) { 
                columnStyles[amountStartCol] = { halign: 'right' }; 
                columnStyles[amountStartCol + 1] = { halign: 'right' }; 
            }
            else if (showIn || showOut) { 
                columnStyles[amountStartCol] = { halign: 'right' }; 
            }

            doc.autoTable({
                head: [tableHeaders],
                body: body,
                foot: foot,
                startY: nextY,
                showFoot: 'lastPage',
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1, lineColor: [200, 200, 200] },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                columnStyles: columnStyles,
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold', halign: 'right' },
                didParseCell: function (data) {
                    if (data.section === 'body' && showStatus) {
                        const statusColIndex = 6;
                        const amountColIndices = [7, 8];
                        const statusText = data.row.cells[statusColIndex].text[0] || '';
                        
                        if (statusText && statusText.toLowerCase() !== 'approved' && amountColIndices.includes(data.column.index)) {
                            data.cell.styles.textColor = [150, 150, 150];
                        }
                    }
                },
                didDrawCell: function (data) {
                    // Index 7 is In, 8 is Out, Status is Index 6
                    const isAmountCol = (data.column.index === 7 || data.column.index === 8);
                    if (data.section === 'body' && isAmountCol && showStatus) {
                        const statusText = (data.row.cells[6].text[0] || '').trim().toLowerCase();
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

            doc.save("Project_Report_{{ $selected_project->project_name ?? 'Export' }}.pdf");
        }

        function openPrintView() {
            const params = new URLSearchParams(window.location.search);
            const url = "{{ route('admin.reports.print') }}?" + params.toString();
            window.open(url, '_blank');
        }
    </script>
    @endif
@endsection
