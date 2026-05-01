@extends('layouts.app')

@section('title', 'Report: Client Receive')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <div>
            <h2>Client Receive Report</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 20px;">
        <form method="GET" action="{{ route('admin.reports.client_receive') }}" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 16px; align-items: end;">
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
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('admin.reports.client_receive') }}" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">Clear</a>
            </div>
            @if(count($report_data) > 0)
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.reports.print', array_merge(request()->query(), ['report_type' => 'client_received'])) }}" target="_blank" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-print"></i> Print
                </a>
                <button type="button" onclick="exportToPDF()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                </button>
                <button type="button" onclick="exportToExcel()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                </button>
            </div>
            @endif
        </form>
    </div>

    <!-- Print Header (Hidden on Screen) -->
    <div class="print-only">
        <table class="report-table" style="width: 100%; border-collapse: collapse; margin-bottom: 0;">
            <tbody>
                <tr>
                    <th style="background-color: #f8f9fa; color: #000; font-size: 20px; padding: 12px; text-transform: uppercase; border: 1px solid #000; text-align: center;">Client Receive Report</th>
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
                <tr>
                    <td style="border: 1px solid #000; padding: 8px; text-align: center;">
                        Generated on: {{ date('Y-m-d H:i') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="glass-panel">
        <div class="table-wrapper">
            <table class="table" id="client-receive-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Invoice No</th>
                        <th>Project Name</th>
                        <th>Client</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount (Tk.)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @forelse($report_data as $data)
                        @php $total += $data->amount; @endphp
                        <tr>
                            <td>{{ $data->payment_date->format('Y-m-d') }}</td>
                            <td style="font-family: monospace;">
                                <a href="{{ route('admin.projects.payments.invoice', $data->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                    {{ $data->invoice_no ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $data->project->project_name }}</td>
                            <td>{{ $data->project->client_name }}</td>
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
                        <th colspan="5" style="text-align: right; font-size: 16px;">Total Received:</th>
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
            body { background: #white !important; color: black !important; }
            .glass-panel { border: none !important; background: transparent !important; padding: 0 !important; }
            .table { color: black !important; border: 1px solid #000 !important; }
            .table th { background: #f8f9fa !important; color: black !important; border: 1px solid #000 !important; }
            .table td { border: 1px solid #000 !important; }
            .badge { border: none !important; background: transparent !important; color: black !important; padding: 0 !important; }
        }
        .print-only { display: none; }
    </style>

    <script>
        function exportToExcel() {
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "CLIENT RECEIVE REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const headerStyle = { alignment: { horizontal: "center" }, font: { bold: true, sz: 14 } };
            const subHeaderStyle = { alignment: { horizontal: "center" }, font: { bold: true } };

            const headerData = [
                [{ v: projectTitle, s: headerStyle }],
                [{ v: projectName, s: subHeaderStyle }]
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
            
            const table = document.getElementById("client-receive-table");
            const tableClone = table.cloneNode(true);
            const tfoot = tableClone.querySelector('tfoot');
            if (tfoot) tfoot.remove();
            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            // Footer
            const footerData = [['Total Received:', '', '', '', '', "Tk. {{ number_format($total ?? 0, 2) }}"]];
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
            XLSX.writeFile(wb, "Client_Receive_Report.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "CLIENT RECEIVE REPORT";
            const projectName = "Project: {{ request('project_id') ? $projects->find(request('project_id'))->project_name : 'All Projects' }}";
            const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

            const body = [];
            document.querySelectorAll('#client-receive-table tbody tr').forEach(row => {
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

            const foot = [[{ content: 'Total Received:', colSpan: 5, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($total ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]];

            doc.autoTable({
                head: [[{content:'Date'}, {content:'Invoice'}, {content:'Project'}, {content:'Client'}, {content:'Method'}, {content:'Amount', styles:{halign:'right'}}]],
                body: body,
                foot: foot,
                startY: doc.lastAutoTable.finalY,
                theme: 'grid',
                styles: { fontSize: 8, halign: 'center', lineWidth: 0.1, lineColor: [200, 200, 200] },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                columnStyles: { 5: { halign: 'right' } },
                footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' }
            });

            doc.save("Client_Receive_Report.pdf");
        }
    </script>
@endsection
