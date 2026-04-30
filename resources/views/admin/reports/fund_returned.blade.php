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
        <form method="GET" action="{{ route('admin.reports.fund_returned') }}" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end;">
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
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                <a href="{{ route('admin.reports.fund_returned') }}" class="btn btn-outline" style="border-color: var(--danger); color: var(--danger);">Clear</a>
            </div>
            @if(count($report_data) > 0)
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.reports.print', array_merge(request()->query(), ['report_type' => 'fund_return'])) }}" target="_blank" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
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
            const table = document.getElementById("returns-report-table");
            const tableClone = table.cloneNode(true);
            const tfoot = tableClone.querySelector('tfoot');
            if (tfoot) tfoot.remove();
            XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

            const footerData = [['Total Returned:', '', '', '', '', "Tk. {{ number_format($total ?? 0, 2) }}"]];
            XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });

            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, "Fund_Returned_Report.xlsx");
        }

        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            const downloadDate = new Date().toLocaleDateString();
            const projectTitle = "FUND RETURNED (FROM PM) REPORT";
            
            const body = [];
            document.querySelectorAll('#returns-report-table tbody tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('td').forEach(cell => rowData.push(cell.innerText.trim()));
                if (rowData.length > 0) body.push(rowData);
            });

            doc.autoTable({
                head: [[{content:'Date'}, {content:'Invoice'}, {content:'Project'}, {content:'Returned By'}, {content:'Method'}, {content:'Amount', styles:{halign:'right'}}]],
                body: body,
                foot: [[{ content: 'Total Returned:', colSpan: 5, styles: { halign: 'right' } }, { content: 'Tk. {{ number_format($total ?? 0, 2) }}', styles: { halign: 'right', fontStyle: 'bold' } }]],
                startY: 20,
                theme: 'grid',
                styles: { fontSize: 8 },
                headStyles: { fillColor: [41, 128, 185] }
            });

            doc.text(projectTitle, 14, 15);
            doc.save("Fund_Returned_Report.pdf");
        }
    </script>
@endsection
