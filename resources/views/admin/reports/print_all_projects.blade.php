<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects Report - Print</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #000; background: #fff; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .header-table th { background-color: transparent; color: #000; font-size: 24px; padding: 10px; text-transform: uppercase; border: 1px solid #000; text-align: center; }
        .header-table td { border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; font-size: 14px; border-bottom: none; }
        
        .data-table { width: 100%; border-collapse: collapse; margin-top: 0; }
        .data-table thead tr td { border: 1px solid #000; border-bottom: none; padding: 8px; }
        .data-table thead tr th { border: 1px solid #000; border-bottom: none; padding: 8px; }
        .data-table thead tr:last-child th { border-bottom: 1px solid #000; }
        .data-table td { border: 1px solid #000; padding: 8px; font-size: 12px; vertical-align: top; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        
        .recorded-by { font-size: 10px; color: #000; margin-top: 4px; font-weight: normal; }
        .rejected { text-decoration: line-through; }
        
        .footer-row th { background-color: transparent; border: 1px solid #000; padding: 10px; font-size: 14px; }
        
        @media print {
            @page { margin: 1cm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <table class="data-table">
        <thead>
            <!-- Main Title -->
            <tr>
                <th colspan="8" style="font-size: 24px; padding: 15px; text-transform: uppercase;">All Projects Report</th>
            </tr>
            <!-- Project Row -->
            <tr>
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Project: {{ $selectedProject ? $selectedProject->project_name : 'All Projects' }}
                </td>
            </tr>
            <!-- Manager Row -->
            @if($selectedProject && $selectedProject->manager)
            <tr>
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Manager: {{ $selectedProject->manager->name }}
                </td>
            </tr>
            @endif
            <!-- Date Row -->
            <tr>
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Date: {{ date('Y-m-d') }}
                </td>
            </tr>
            <!-- Printed By Row -->
            <tr>
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Printed By: {{ $printedBy }}
                </td>
            </tr>
            @if(request('from_date') || request('to_date'))
            <tr>
                <td colspan="8" style="text-align: center; font-weight: bold;">
                    Period: {{ request('from_date') ?: 'Start' }} to {{ request('to_date') ?: 'End' }}
                </td>
            </tr>
            @endif
            <!-- Table Column Headers -->
            <tr>
                <th style="width: 5%;">SL</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 15%;">Project</th>
                <th style="width: 15%;">Invoice No</th>
                <th style="width: 25%;">Description</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 10%;">Credit (In)</th>
                <th style="width: 10%;">Debit (Out)</th>
            </tr>
        </thead>
        <tbody>
            @php $sl = 1; @endphp
            @foreach($report_data as $data)
            <tr>
                <td class="text-center">{{ $sl++ }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($data['date'])->format('Y-m-d') }}</td>
                <td class="text-center">{{ $data['project_name'] }}</td>
                <td class="text-center" style="font-family: monospace;">{{ $data['invoice_no'] }}</td>
                <td>
                    <div>{{ $data['description'] }}</div>
                    <div class="recorded-by">Recorded By: {{ $data['recorded_by'] }}</div>
                </td>
                <td class="text-center">{{ ucfirst($data['status']) }}</td>
                <td class="text-right bold">
                    {{ $data['credit'] > 0 ? 'Tk. ' . number_format($data['credit']) : '-' }}
                </td>
                <td class="text-right bold {{ $data['status'] === 'rejected' ? 'rejected' : '' }}">
                    {{ $data['debit'] > 0 ? 'Tk. ' . number_format($data['debit']) : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <th colspan="6" class="text-right">Total:</th>
                <th class="text-right bold">Tk. {{ number_format($totalCredit) }}</th>
                <th class="text-right bold">Tk. {{ number_format($totalDebit) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
