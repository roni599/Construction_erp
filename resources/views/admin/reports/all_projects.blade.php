@extends('layouts.app')

@section('title', 'All Projects Report')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <div>
            <h2>All Projects Report</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 20px;">
        <form method="GET" action="{{ route('admin.reports.all_projects') }}">
            <div class="dashboard-grid" style="margin-bottom: 24px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-control" style="background: rgba(0,0,0,0.8);">
                        <option value="">All Projects</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Invoice No</label>
                    <input type="text" name="invoice_no" class="form-control" placeholder="Search..." value="{{ request('invoice_no') }}">
                </div>
            </div>

            <div class="btn-group" style="flex-wrap: wrap; flex-direction: row !important; justify-content: space-between;">
                <div class="btn-group" style="flex: 1; min-width: 200px; flex-direction: row !important;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="fas fa-search"></i> Filter</button>
                    <a href="{{ route('admin.reports.all_projects') }}" class="btn btn-outline" style="flex: 1; border-color: var(--danger); color: var(--danger);">Clear</a>
                </div>

                @if(count($report_data) > 0)
                <div class="btn-group" style="flex: 1.5; min-width: 300px; justify-content: flex-end; flex-direction: row !important;">
                    <a href="{{ route('admin.reports.all_projects.print', request()->query()) }}" target="_blank" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-print"></i> Print
                    </a>
                    <button type="button" onclick="exportToPDF()" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                    </button>
                    <button type="button" onclick="exportToExcel()" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                    </button>
                </div>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Report Container -->
    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table" id="all-projects-report-table">
                <thead>
                    <!-- Print-Only Header Rows -->
                    <tr class="print-only">
                        <th colspan="8" style="background: #f8f9fa !important; color: #000 !important; font-size: 20px; padding: 12px; text-transform: uppercase; border: 1px solid #000; text-align: center;">All Projects Report</th>
                    </tr>
                    <tr class="print-only">
                        <th colspan="8" style="background: #fff !important; color: #000 !important; border: 1px solid #000; border-top: none; padding: 8px; text-align: center; font-weight: bold;">
                            Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}
                        </th>
                    </tr>
                    <tr class="print-only">
                        <th colspan="8" style="background: #fff !important; color: #000 !important; border: 1px solid #000; border-top: none; padding: 8px; text-align: center; font-weight: bold;">
                            Date: {{ date('Y-m-d') }}
                        </th>
                    </tr>
                    <tr class="print-only">
                        <th colspan="8" style="background: #fff !important; color: #000 !important; border: 1px solid #000; border-top: none; padding: 8px; text-align: center; font-weight: bold;">
                            Printed By: {{ auth()->user()->name }}
                        </th>
                    </tr>
                    @if(request('from_date') || request('to_date'))
                    <tr class="print-only">
                        <th colspan="8" style="background: #fff !important; color: #000 !important; border: 1px solid #000; border-top: none; padding: 8px; text-align: center;">
                            Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}
                        </th>
                    </tr>
                    @endif
                    <!-- Standard Table Headers -->
                    <tr>
                        <th style="width: 5%;">SL</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 15%;">Project</th>
                        <th style="width: 12%;">Invoice No</th>
                        <th style="width: 18%;">Description</th>
                        <th style="width: 10%; text-align: center;">Status</th>
                        <th style="width: 15%; text-align: right;">Credit (In)</th>
                        <th style="width: 15%; text-align: right;">Debit (Out)</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $sl = 1; 
                    @endphp
                    @forelse($report_data as $data)
                        <tr>
                            <td>{{ $sl++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}</td>
                            <td>{{ $data['project_name'] }}</td>
                            <td style="font-family: monospace;">{{ $data['invoice_no'] }}</td>
                            <td>
                                <div>{{ $data['description'] }}</div>
                                <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                    Recorded By: {{ $data['recorded_by'] }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: {{ $data['status'] === 'approved' ? 'rgba(40, 167, 69, 0.1)' : ($data['status'] === 'rejected' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)') }}; color: {{ $data['status'] === 'approved' ? 'var(--success)' : ($data['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }}; border: 1px solid {{ $data['status'] === 'approved' ? 'var(--success)' : ($data['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }};">
                                    {{ ucfirst($data['status']) }}
                                </span>
                            </td>
                            <td style="text-align: right; color: var(--success); font-weight: bold;">
                                {{ $data['credit'] > 0 ? 'Tk. ' . number_format($data['credit']) : '-' }}
                            </td>
                            <td style="text-align: right; color: var(--danger); font-weight: bold; {{ $data['status'] === 'rejected' ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                {{ $data['debit'] > 0 ? 'Tk. ' . number_format($data['debit']) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 40px;">No data found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($report_data) > 0)
                <tfoot>
                    <tr style="border-top: 2px solid var(--border-color);">
                        <th colspan="6" style="text-align: right; font-size: 16px;">Total:</th>
                        <th style="text-align: right; color: var(--success); font-size: 16px;">Tk. {{ number_format($totalCredit) }}</th>
                        <th style="text-align: right; color: var(--danger); font-size: 16px;">Tk. {{ number_format($totalDebit) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: table-row !important; }
            tr.print-only { display: table-row !important; }
            body { background: white !important; color: black !important; margin: 0; padding: 0; }
            .glass-panel { border: none !important; background: transparent !important; padding: 0 !important; margin: 0 !important; }
            .table-wrapper { margin: 0 !important; padding: 0 !important; }
            .table { color: black !important; border: 1px solid #000 !important; margin-top: 0 !important; }
            .table th { color: black !important; border: 1px solid #000 !important; }
            .table td { border: 1px solid #000 !important; }
            .badge { border: none !important; background: transparent !important; color: black !important; padding: 0 !important; }
        }
        .print-only { display: none; }
    </style>

    <script>
        function exportToExcel() {
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "ALL PROJECTS REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const headerData = [
                [{ v: projectTitle, s: { font: { bold: true, sz: 14 }, alignment: { horizontal: "center" } } }],
                [{ v: projectName, s: { font: { bold: true }, alignment: { horizontal: "center" } } }],
                [{ v: "Date: {{ date('Y-m-d') }}", s: { alignment: { horizontal: "center" } } }],
                [{ v: "Printed By: {{ auth()->user()->name }}", s: { alignment: { horizontal: "center" } } }]
            ];
            if (reportPeriod) headerData.push([{ v: reportPeriod, s: { alignment: { horizontal: "center" } } }]);
            headerData.push([]); 

            const ws = XLSX.utils.aoa_to_sheet(headerData);
            const colCount = 8;
            const merges = [];
            for (let i = 0; i < headerData.length - 1; i++) {
                merges.push({ s: { r: i, c: 0 }, e: { r: i, c: colCount - 1 } });
            }

            const table = document.getElementById("all-projects-report-table");
            const tableClone = table.cloneNode(true);
            const tfoot = tableClone.querySelector('tfoot');
            if (tfoot) tfoot.remove();
            
            // Remove duplicate header rows from table clone
            tableClone.querySelectorAll('.print-only').forEach(el => el.remove());
            
            // Clean up description HTML for Excel
            const rows = tableClone.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const descCell = row.cells[3];
                if(descCell) {
                    const descDiv = descCell.querySelector('div:first-child');
                    const userDiv = descCell.querySelector('div:last-child');
                    if(descDiv && userDiv) {
                        descCell.innerText = descDiv.innerText + ' (' + userDiv.innerText.trim() + ')';
                    }
                }
            });

            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            // Handle strike-through for rejected items
            const bodyRange = XLSX.utils.decode_range(ws['!ref']);
            const totalExcelRows = bodyRange.e.r + 1;
            const dataRowsOnly = Array.from(rows);
            for (let i = 0; i < dataRowsOnly.length; i++) {
                const statusCell = dataRowsOnly[i].cells[4];
                if (statusCell && statusCell.innerText.trim().toLowerCase() === 'rejected') {
                    const excelRow = (totalExcelRows - dataRowsOnly.length) + i;
                    const amountCellRef = XLSX.utils.encode_cell({ r: excelRow, c: 7 });
                    if (ws[amountCellRef]) {
                        if (!ws[amountCellRef].s) ws[amountCellRef].s = {};
                        if (!ws[amountCellRef].s.font) ws[amountCellRef].s.font = {};
                        ws[amountCellRef].s.font.strike = true;
                    }
                }
            }

            const footerData = [['Total:', '', '', '', '', '', "Tk. {{ number_format($totalCredit ?? 0) }}", "Tk. {{ number_format($totalDebit ?? 0) }}"]];
            const startFooterRow = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
            XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });
            merges.push({ s: { r: startFooterRow, c: 0 }, e: { r: startFooterRow, c: 5 } });

            const cellRef = XLSX.utils.encode_cell({ r: startFooterRow, c: colCount - 1 });
            if (ws[cellRef]) ws[cellRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };
            const cellRefCredit = XLSX.utils.encode_cell({ r: startFooterRow, c: colCount - 2 });
            if (ws[cellRefCredit]) ws[cellRefCredit].s = { alignment: { horizontal: "right" }, font: { bold: true } };
            
            const labelRef = XLSX.utils.encode_cell({ r: startFooterRow, c: 0 });
            if (ws[labelRef]) ws[labelRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };

            ws['!merges'] = merges;
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "All_Projects_Report.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const projectTitle = "ALL PROJECTS REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const body = [];
            document.querySelectorAll('#all-projects-report-table tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach((cell, index) => {
                    if (index === 3) {
                        const descDiv = cell.querySelector('div:first-child');
                        const userDiv = cell.querySelector('div:last-child');
                        if(descDiv && userDiv) {
                            rowData.push(descDiv.innerText + '\n' + userDiv.innerText.trim());
                        } else {
                            rowData.push(cell.innerText.trim());
                        }
                    } else {
                        rowData.push(cell.innerText.trim());
                    }
                });
                
                const status = row.cells[4] ? row.cells[4].innerText.trim().toLowerCase() : '';
                body.push({
                    data: rowData,
                    isRejected: status === 'rejected'
                });
            });

            const downloadDate = "{{ date('Y-m-d') }}";
            const printedBy = "{{ auth()->user()->name }}";
            
            const pdfHead = [
                [{ content: projectTitle, colSpan: 8, styles: { halign: 'center', fontSize: 14, fontStyle: 'bold', fillColor: [240, 240, 240] } }],
                [{ content: projectName, colSpan: 8, styles: { halign: 'center', fontSize: 10, fontStyle: 'bold' } }],
                [{ content: 'Date: ' + downloadDate + ' | Printed By: ' + printedBy, colSpan: 8, styles: { halign: 'center', fontSize: 9 } }]
            ];
            
            if (reportPeriod) {
                pdfHead.push([{ content: reportPeriod, colSpan: 8, styles: { halign: 'center', fontSize: 9 } }]);
            }

            // Standard Column Headers
            pdfHead.push([
                {content:'SL'}, {content:'Date'}, {content:'Project'}, {content:'Invoice No'}, {content:'Description'}, {content:'Status'}, {content:'Credit (In)', styles:{halign:'right'}}, {content:'Debit (Out)', styles:{halign:'right'}}
            ]);

            const foot = [[{ content: 'Total:', colSpan: 6, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($totalCredit ?? 0) }}', styles: { halign: 'right', fontStyle: 'bold' } }, { content: 'Tk. {{ number_format($totalDebit ?? 0) }}', styles: { halign: 'right', fontStyle: 'bold' } }]];

            doc.autoTable({
                head: pdfHead,
                body: body.map(r => r.data),
                foot: foot,
                startY: 15,
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1, lineColor: [0, 0, 0], textColor: [0, 0, 0] },
                headStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' },
                columnStyles: { 6: { halign: 'right' }, 7: { halign: 'right' } },
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' },
                didParseCell: function(data) {
                    if (data.section === 'body' && data.column.index === 7) {
                        const rowData = body[data.row.index];
                        if (rowData && rowData.isRejected) {
                            data.cell.styles.textColor = [150, 150, 150];
                            data.cell.styles.fontStyle = 'italic';
                        }
                    }
                },
                margin: { left: 14, right: 14 }
            });
            doc.save("All_Projects_Report.pdf");
        }
    </script>
@endsection
