@extends('layouts.app')

@section('title', 'Report: Fund Returned (PM)')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <div>
            <h2>Fund Returned (from PM) Report</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 20px;">
        <form method="GET" action="{{ route('admin.reports.fund_returned') }}">
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
                    <a href="{{ route('admin.reports.fund_returned') }}" class="btn btn-outline" style="flex: 1; border-color: var(--danger); color: var(--danger);">Clear</a>
                </div>

                @if(count($report_data) > 0)
                <div class="btn-group" style="flex: 1.5; min-width: 300px; justify-content: flex-end; flex-direction: row !important;">
                    <a href="{{ route('admin.reports.print', array_merge(request()->query(), ['report_type' => 'fund_return'])) }}" target="_blank" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
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

    <!-- Print Header -->
    <div class="print-only">
        <table class="report-table" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tbody>
                <tr>
                    <th style="background-color: #f8f9fa; color: #000; font-size: 20px; padding: 12px; text-transform: uppercase; border: 1px solid #000; text-align: center;">Fund Returned (from PM) Report</th>
                </tr>
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">
                        Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}
                    </td>
                </tr>
                @if(request('from_date') || request('to_date'))
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                        Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table" id="returns-report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Returned By</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @forelse($report_data as $data)
                        @php $total += $data->amount; @endphp
                        <tr>
                            <td>{{ $data->return_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('shared.returns.invoice', $data->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;">
                                    {{ $data->invoice_no ?? 'RET-'.$data->id }}
                                </a>
                            </td>
                            <td>{{ $data->project->project_name }}</td>
                            <td>{{ $data->employee->name }}</td>
                            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $data->payment_method) }}</td>
                            <td style="text-align: right; color: var(--success); font-weight: bold;">Tk. {{ number_format($data->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 40px;">No data found for the selected filters.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($report_data) > 0)
                <tfoot>
                    <tr style="border-top: 2px solid var(--border-color);">
                        <th colspan="5" style="text-align: right; font-size: 16px;">Total Returned:</th>
                        <th style="text-align: right; color: var(--success); font-size: 16px;">Tk. {{ number_format($total, 2) }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        <div class="custom-pagination" style="margin-top: 24px;">
            {{ $report_data->appends(request()->query())->links() }}
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; color: black !important; }
            .glass-panel { border: none !important; background: transparent !important; padding: 0 !important; }
            .table { color: black !important; border: 1px solid #000 !important; }
            .table th { background: #f8f9fa !important; color: black !important; border: 1px solid #000 !important; }
            .table td { border: 1px solid #000 !important; }
        }
        .print-only { display: none; }
    </style>

    <script>
        function exportToExcel() {
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "FUND RETURNED (FROM PM) REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const headerData = [
                [{ v: projectTitle, s: { font: { bold: true, sz: 14 }, alignment: { horizontal: "center" } } }],
                [{ v: projectName, s: { font: { bold: true }, alignment: { horizontal: "center" } } }]
            ];
            if (reportPeriod) headerData.push([{ v: reportPeriod, s: { alignment: { horizontal: "center" } } }]);
            headerData.push([{ v: "Date: " + downloadDate, s: { alignment: { horizontal: "center" } } }]);
            headerData.push([]); 

            const ws = XLSX.utils.aoa_to_sheet(headerData);
            const colCount = 6;
            const merges = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: colCount - 1 } },
                { s: { r: 1, c: 0 }, e: { r: 1, c: colCount - 1 } }
            ];
            let currentRow = 2;
            if (reportPeriod) { merges.push({ s: { r: currentRow, c: 0 }, e: { r: currentRow, c: colCount - 1 } }); currentRow++; }
            merges.push({ s: { r: currentRow, c: 0 }, e: { r: currentRow, c: colCount - 1 } });

            const table = document.getElementById("returns-report-table");
            const tableClone = table.cloneNode(true);
            const tfoot = tableClone.querySelector('tfoot');
            if (tfoot) tfoot.remove();
            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            const footerData = [['Total Returned:', '', '', '', '', "Tk. {{ number_format($total ?? 0, 2) }}"]];
            const startFooterRow = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
            XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });
            merges.push({ s: { r: startFooterRow, c: 0 }, e: { r: startFooterRow, c: 4 } });

            const cellRef = XLSX.utils.encode_cell({ r: startFooterRow, c: colCount - 1 });
            if (ws[cellRef]) ws[cellRef].s = { alignment: { horizontal: "left" }, font: { bold: true } };
            const labelRef = XLSX.utils.encode_cell({ r: startFooterRow, c: 0 });
            if (ws[labelRef]) ws[labelRef].s = { alignment: { horizontal: "right" }, font: { bold: true } };

            ws['!merges'] = merges;
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "Fund_Returned_Report.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "FUND RETURNED (FROM PM) REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const body = [];
            document.querySelectorAll('#returns-report-table tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => rowData.push(cell.innerText.trim()));
                if (rowData.length > 0) body.push(rowData);
            });

            const headerRows = [
                [{ content: projectTitle, styles: { halign: 'center', fontSize: 14, fontStyle: 'bold', fillColor: [248, 249, 250] } }],
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

            const foot = [[{ content: 'Total Returned:', colSpan: 5, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($total ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]];

            doc.autoTable({
                head: [[{content:'Date'}, {content:'Invoice'}, {content:'Project'}, {content:'Returned By'}, {content:'Method'}, {content:'Amount', styles:{halign:'right'}}]],
                body: body,
                foot: foot,
                startY: doc.lastAutoTable.finalY,
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1, lineColor: [200, 200, 200] },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                columnStyles: { 5: { halign: 'right' } },
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' }
            });
            doc.save("Fund_Returned_Report.pdf");
        }
    </script>
@endsection
