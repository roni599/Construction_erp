<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Breakdown - {{ $selectedProject->project_name ?? 'Report' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #fff;
            color: #000;
            padding-bottom: 80px;
        }
        .report-header {
            border: 1px solid #000;
            border-bottom: none;
            margin-bottom: 0;
        }
        .header-row {
            font-size: 11px;
            line-height: 1.2;
        }
        .table thead th {
            background-color: #f8f9fa !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        .table td {
            border: 1px solid #000 !important;
            font-size: 11px;
            vertical-align: middle;
        }
        .recorded-by {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }
        .fixed-print-btn {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 12px 25px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        @media print {
            .fixed-print-btn {
                display: none !important;
            }
            body {
                padding-bottom: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        .text-danger { color: #dc3545 !important; }
        .text-warning { color: #ffc107 !important; }
        .text-success { color: #198754 !important; }
        .text-primary { color: #0d6efd !important; }
    </style>
</head>
<body>

<div class="container-fluid">
    <table class="table table-bordered w-100" style="margin-top: 20px !important;">
        <thead>
            <tr style="background: #f0f0f0;">
                <th colspan="10" style="text-align: center; font-size: 18px; padding: 5px 10px; border: 1px solid #000;">PROJECT BREAKDOWN REPORT</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: center; padding: 5px 10px; border: 1px solid #000;"><strong>Project Name:</strong> {{ $selectedProject->project_name }}</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: center; padding: 5px 10px; border: 1px solid #000;"><strong>Project Manager:</strong> {{ $selectedProject->manager->name ?? 'N/A' }}</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: center; padding: 5px 10px; border: 1px solid #000;"><strong>Printed By:</strong> {{ $printedBy }}</th>
            </tr>
            <tr>
                <th colspan="10" style="text-align: center; padding: 5px 10px; border: 1px solid #000;"><strong>Report Date:</strong> {{ date('Y-m-d') }}</th>
            </tr>
            @if(request('from_date') || request('to_date'))
            <tr>
                <th colspan="10" style="text-align: center; padding: 5px 10px; border: 1px solid #000;"><strong>Report Period:</strong> {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}</th>
            </tr>
            @endif
            
            <tr style="background: #f8f9fa;">
                <th style="width: 40px;">SL</th>
                <th style="width: 90px;">Date</th>
                <th style="width: 100px;">Invoice</th>
                <th style="width: 110px;">Type</th>
                <th style="width: 90px;">Method</th>
                <th style="width: 100px;">Category</th>
                <th>Description</th>
                <th style="width: 100px;">Credit (In)</th>
                <th style="width: 100px;">Debit (Out)</th>
                <th style="width: 110px;">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $sl = 1; @endphp
            @forelse($report_data as $data)
                <tr>
                    <td class="text-center">{{ $sl++ }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}</td>
                    <td class="text-center fw-bold" style="font-family: monospace;">{{ $data['invoice_no'] }}</td>
                    <td class="text-center fw-bold {{ $data['type'] === 'Expense' ? 'text-danger' : ($data['type'] === 'Fund Returned' ? 'text-warning' : ($data['type'] === 'Client Payment' ? 'text-success' : 'text-primary')) }}">
                        {{ $data['type'] }}
                    </td>
                    <td class="text-center">{{ $data['method'] }}</td>
                    <td class="text-center">{{ $data['category'] }}</td>
                    <td>
                        <div>{{ $data['description'] }}</div>
                        <div class="recorded-by">Recorded By: {{ $data['recorded_by'] }}</div>
                    </td>
                    <td class="text-end fw-bold text-success">
                        {{ $data['credit'] > 0 ? number_format($data['credit']) : '-' }}
                    </td>
                    <td class="text-end fw-bold text-danger" style="{{ !$data['is_calculable'] ? 'text-decoration: line-through; opacity: 0.6;' : '' }}">
                        {{ $data['debit'] > 0 ? number_format($data['debit']) : '-' }}
                    </td>
                    <td class="text-end fw-bold text-primary">
                        {{ number_format($data['balance']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4">No transactions found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold bg-light">
                <td colspan="7" class="text-end">Grand Total:</td>
                <td class="text-end text-success">{{ number_format($totalCredit) }}</td>
                <td class="text-end text-danger">{{ number_format($totalDebit) }}</td>
                <td class="text-end text-primary">{{ number_format($totalCredit - $totalDebit) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

<button onclick="window.print()" class="btn btn-primary fixed-print-btn no-print">
    <i class="fas fa-print me-2"></i> Print Report
</button>

<script>
    // Automatically trigger print on load (optional, but requested by some)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>
