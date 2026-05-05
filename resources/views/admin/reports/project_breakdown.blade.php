@extends('layouts.app')

@section('title', 'Project Breakdown Report')

@section('content')
    <!-- External Libraries for Export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx-js-style@1.2.0/dist/xlsx.bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>

    <style>
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        .print-only { display: none; }
        @media print {
            .no-print { display: none !important; }
            .printable-area { width: 100% !important; margin: 0 !important; padding: 0 !important; }
            table { border-collapse: collapse !important; width: 100% !important; border: 1px solid #000 !important; }
            th, td { border: 1px solid #000 !important; padding: 8px !important; color: #000 !important; font-size: 10px !important; }
            .print-only { display: table-row !important; }
            a { color: #000 !important; text-decoration: none !important; }
        }
    </style>

    <div class="flex-between no-print" style="margin-bottom: 32px;">
        <h2>Project Breakdown Report</h2>
    </div>

    <!-- Filter Section -->
    <div class="glass-panel no-print" style="margin-bottom: 32px;">
        <form action="{{ route('admin.reports.project_breakdown') }}" method="GET">
            <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 0 !important;">
                <div class="form-group">
                    <label class="form-label">Select Project <span class="text-danger">*</span></label>
                    <select name="project_id" class="form-control" required style="background: rgba(0,0,0,0.8);">
                        <option value="">-- Choose Project --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
            </div>

            <div class="btn-group" style="margin-top: 24px; flex-wrap: wrap; flex-direction: row !important; justify-content: space-between;">
                <div class="btn-group" style="flex: 1; min-width: 200px; flex-direction: row !important;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.reports.project_breakdown') }}" class="btn btn-outline" style="flex: 1; border-color: var(--danger); color: var(--danger);">
                        <i class="fas fa-redo"></i> Clear
                    </a>
                </div>
                
                <div class="btn-group" style="flex: 1.5; min-width: 300px; justify-content: flex-end; flex-direction: row !important;">
                    @php $hasData = count($report_data) > 0; @endphp
                    <button type="button" onclick="{{ $hasData ? 'openPrintView()' : 'alert(\'Please search first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" onclick="{{ $hasData ? 'exportToPDF()' : 'alert(\'Please search first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-pdf" style="color: #e74c3c;"></i> PDF
                    </button>
                    <button type="button" onclick="{{ $hasData ? 'exportToExcel()' : 'alert(\'Please search first!\')' }}" class="btn btn-outline" style="flex: 1; font-size: 13px; padding: 8px 12px !important;">
                        <i class="fas fa-file-excel" style="color: #27ae60;"></i> Excel
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(request('project_id'))
        <div id="report-container">
            <div class="glass-panel no-print" style="margin-bottom: 24px; padding: 20px; text-align: center;">
                <h2 style="margin-bottom: 8px;">Project Breakdown Report</h2>
                <h3 style="color: var(--accent-blue); margin-bottom: 12px;">{{ $selectedProject->project_name }}</h3>
                @if(request('from_date') || request('to_date'))
                    <p style="color: var(--text-secondary);">
                        Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}
                    </p>
                @endif
            </div>

            <!-- Table Section -->
            <div class="glass-panel printable-area">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="breakdown-report-table" style="margin-bottom: 0;">
                        <thead>
                            <!-- Print Header -->
                            <tr class="print-only">
                                <th colspan="11" style="text-align: center; font-size: 20px; padding: 15px; text-transform: uppercase;">Project Breakdown Report</th>
                            </tr>
                            <tr class="print-only">
                                <th colspan="11" style="text-align: center; border-top: none;">Project: {{ $selectedProject->project_name }}</th>
                            </tr>
                            <tr class="print-only">
                                <th colspan="11" style="text-align: center; border-top: none;">Date: {{ date('Y-m-d') }} | Printed By: {{ auth()->user()->name }}</th>
                            </tr>
                            @if(request('from_date') || request('to_date'))
                            <tr class="print-only">
                                <th colspan="11" style="text-align: center; border-top: none;">Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}</th>
                            </tr>
                            @endif
                            
                            <tr style="background: rgba(255,255,255,0.05);">
                                <th style="width: 50px;">SL</th>
                                <th style="width: 100px;">Date</th>
                                <th style="width: 120px;">Invoice</th>
                                <th style="width: 120px;">Type</th>
                                <th style="width: 100px;">Method</th>
                                <th style="width: 120px;">Category</th>
                                <th>Description</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th class="text-end" style="width: 120px;">Credit (In)</th>
                                <th class="text-end" style="width: 120px;">Debit (Out)</th>
                                <th class="text-end" style="width: 120px;">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sl = 1; @endphp
                            @forelse($report_data as $data)
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>{{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}</td>
                                    <td style="font-family: monospace;">
                                        <a href="{{ $data['invoice_url'] }}" target="_blank" style="color: #3498db; text-decoration: none; font-weight: bold;">
                                            {{ $data['invoice_no'] }}
                                        </a>
                                    </td>
                                    <td class="fw-bold" style="color: {{ $data['type'] === 'Expense' ? 'var(--danger)' : ($data['type'] === 'Fund Returned' ? 'var(--accent-yellow)' : ($data['type'] === 'Client Payment' ? 'var(--success)' : 'var(--accent-blue)')) }} !important;">
                                        {{ $data['type'] }}
                                    </td>
                                    <td>{{ $data['method'] }}</td>
                                    <td>{{ $data['category'] }}</td>
                                    <td>
                                        <div>{{ $data['description'] }}</div>
                                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 4px;">
                                            Recorded By: {{ $data['recorded_by'] }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background: {{ $data['status'] === 'approved' ? 'rgba(40, 167, 69, 0.1)' : ($data['status'] === 'rejected' ? 'rgba(220, 53, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)') }}; color: {{ $data['status'] === 'approved' ? 'var(--success)' : ($data['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }}; border: 1px solid {{ $data['status'] === 'approved' ? 'var(--success)' : ($data['status'] === 'rejected' ? 'var(--danger)' : 'var(--accent-yellow)') }};">
                                            {{ ucfirst($data['status']) }}
                                        </span>
                                    </td>
                                    <td class="text-end text-success fw-bold">
                                        {{ $data['credit'] > 0 ? 'Tk. ' . number_format($data['credit']) : '-' }}
                                    </td>
                                    <td class="text-end text-danger fw-bold" style="{{ !$data['is_calculable'] ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                                        {{ $data['debit'] > 0 ? 'Tk. ' . number_format($data['debit']) : '-' }}
                                    </td>
                                    <td class="text-end fw-bold" style="color: var(--accent-blue);">
                                        Tk. {{ number_format($data['balance']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-secondary">
                                        No transactions found for the selected project and criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 2px solid var(--border-color); background: rgba(255,255,255,0.05);">
                                <td colspan="8" class="text-end fw-bold">Grand Total:</td>
                                <td class="text-end text-success fw-bold">Tk. {{ number_format($totalCredit) }}</td>
                                <td class="text-end text-danger fw-bold">Tk. {{ number_format($totalDebit) }}</td>
                                <td class="text-end fw-bold" style="color: var(--accent-blue);">Tk. {{ number_format($totalCredit - $totalDebit) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="glass-panel text-center py-5 no-print">
            <i class="fas fa-search fa-3x text-light mb-3" style="opacity: 0.2;"></i>
            <h5>Please select a project to view the breakdown report.</h5>
        </div>
    @endif

<script>
    function openPrintView() {
        const projectId = "{{ request('project_id') }}";
        const fromDate = "{{ request('from_date') }}";
        const toDate = "{{ request('to_date') }}";
        
        const url = `{{ route('admin.reports.project_breakdown_print') }}?project_id=${projectId}&from_date=${fromDate}&to_date=${toDate}`;
        window.open(url, '_blank');
    }

    function exportToExcel() {
        const projectName = "{{ $selectedProject->project_name ?? '' }}";
        const downloadDate = "{{ date('Y-m-d') }}";
        const printedBy = "{{ auth()->user()->name }}";
        const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

        const headerRows = [
            ['PROJECT BREAKDOWN REPORT'],
            ['Project: ' + projectName],
            ['Date: ' + downloadDate + ' | Printed By: ' + printedBy]
        ];
        if (reportPeriod) headerRows.push([reportPeriod]);
        headerRows.push([]); 

        const ws = XLSX.utils.aoa_to_sheet(headerRows);
        const colCount = 11;
        
        // Style headers: Center alignment
        const range = XLSX.utils.decode_range(ws['!ref']);
        for (let r = 0; r <= headerRows.length - 2; r++) {
            const cellRef = XLSX.utils.encode_cell({ r: r, c: 0 });
            if (!ws[cellRef]) ws[cellRef] = { t: 's', v: '' };
            ws[cellRef].s = {
                alignment: { horizontal: 'center', vertical: 'center' },
                font: { bold: true, size: r === 0 ? 14 : 11 }
            };
        }

        const merges = [];
        for (let i = 0; i < headerRows.length - 1; i++) {
            merges.push({ s: { r: i, c: 0 }, e: { r: i, c: colCount - 1 } });
        }

        const table = document.getElementById("breakdown-report-table");
        const tableClone = table.cloneNode(true);
        tableClone.querySelectorAll('.print-only').forEach(el => el.remove());
        
        const tfoot = tableClone.querySelector('tfoot');
        if (tfoot) tfoot.remove();

        XLSX.utils.sheet_add_dom(ws, tableClone, { origin: -1 });

        // Add Footer with Right Alignment
        const startFooterRow = XLSX.utils.decode_range(ws['!ref']).e.r + 1;
        const footerData = [['Grand Total:', '', '', '', '', '', '', '', "Tk. {{ number_format($totalCredit ?? 0) }}", "Tk. {{ number_format($totalDebit ?? 0) }}", "Tk. {{ number_format($totalCredit - $totalDebit ?? 0) }}"]];
        XLSX.utils.sheet_add_aoa(ws, footerData, { origin: -1 });
        
        // Merge footer label and align right
        merges.push({ s: { r: startFooterRow, c: 0 }, e: { r: startFooterRow, c: 7 } });
        
        // Apply right alignment to footer cells
        [0, 8, 9, 10].forEach(c => {
            const cellRef = XLSX.utils.encode_cell({ r: startFooterRow, c: c });
            if (!ws[cellRef]) ws[cellRef] = { t: 's', v: '' };
            ws[cellRef].s = {
                alignment: { horizontal: 'right' },
                font: { bold: true }
            };
        });

        ws['!merges'] = merges;
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Breakdown");
        XLSX.writeFile(wb, "Project_Breakdown_" + projectName.replace(/\s+/g, '_') + ".xlsx");
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'mm', 'a4'); 
        const projectName = "{{ $selectedProject->project_name ?? '' }}";
        const downloadDate = "{{ date('Y-m-d') }}";
        const printedBy = "{{ auth()->user()->name }}";
        const reportPeriod = "@if(request('from_date') || request('to_date'))Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}@endif";

        const body = [];
        document.querySelectorAll('#breakdown-report-table tbody tr').forEach(row => {
            if (row.cells.length > 1) {
                const rowData = Array.from(row.cells).map(cell => cell.innerText.trim());
                body.push(rowData);
            }
        });

        const pdfHead = [
            [{ content: 'PROJECT BREAKDOWN REPORT', colSpan: 11, styles: { halign: 'center', fontSize: 14, fontStyle: 'bold', fillColor: [240, 240, 240] } }],
            [{ content: 'Project: ' + projectName, colSpan: 11, styles: { halign: 'center', fontSize: 10, fontStyle: 'bold' } }],
            [{ content: 'Date: ' + downloadDate + ' | Printed By: ' + printedBy, colSpan: 11, styles: { halign: 'center', fontSize: 9 } }]
        ];
        if (reportPeriod) pdfHead.push([{ content: reportPeriod, colSpan: 11, styles: { halign: 'center', fontSize: 9 } }]);

        pdfHead.push([
            'SL', 'Date', 'Invoice', 'Type', 'Method', 'Category', 'Description', 'Status', 'Credit', 'Debit', 'Balance'
        ]);

        const foot = [[{ content: 'Grand Total:', colSpan: 8, styles: { halign: 'right' } }, 'Tk. {{ number_format($totalCredit) }}', 'Tk. {{ number_format($totalDebit) }}', 'Tk. {{ number_format($totalCredit - $totalDebit) }}']];

        doc.autoTable({
            head: pdfHead,
            body: body,
            foot: foot,
            startY: 15,
            theme: 'grid',
            styles: { fontSize: 7, halign: 'center', lineWidth: 0.1, lineColor: [0, 0, 0] },
            headStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' },
            footStyles: { fillColor: [255, 255, 255], textColor: [0, 0, 0], fontStyle: 'bold' },
            margin: { left: 10, right: 10 }
        });

        doc.save("Project_Breakdown_" + projectName + ".pdf");
    }
</script>
@endsection
