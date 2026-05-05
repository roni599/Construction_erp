<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ledger - {{ $project->project_name }}</title>
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; padding: 20px; color: #000; line-height: 1.2; background: #fff; }
        .report-table { width: 100%; border-collapse: collapse; margin: 0; font-size: 11px; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 6px 4px; text-align: center; color: #000; }
        .report-table th { background: #fff !important; font-weight: bold; }
        .report-table .text-right { text-align: right; }
        
        .footer-row td { font-weight: bold; background: #fff !important; border: 1px solid #000 !important; }
        .footer-label { text-align: right !important; }
        .footer-value { text-align: right !important; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .report-table { page-break-after: auto; }
            .report-table thead { display: table-header-group !important; }
            .report-table tr { page-break-inside: avoid !important; }
        }
    </style>
</head>
<body onload="window.print();">
    <table class="report-table">
        <thead>
            <tr>
                <th colspan="7" style="background: #fff; color: #000; font-size: 18px; padding: 10px; border: 1px solid #000;">PROJECT HISTORY (LEDGER)</th>
            </tr>
            <tr>
                <th colspan="7" style="padding: 8px; border: 1px solid #000; font-weight: bold; text-align: center;">
                    Project: {{ $project->project_name }}
                </th>
            </tr>
            <tr>
                <th colspan="7" style="padding: 8px; border: 1px solid #000; text-align: center; font-weight: normal;">
                    Print Date: {{ date('Y-m-d') }}
                </th>
            </tr>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 12%;">Invoice No</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 20%;">Description</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 15%;">Credit (In)</th>
                <th style="width: 15%;">Debit (Out)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item['date'])->format('d M, Y') }}</td>
                    <td>{{ $item['id'] }}</td>
                    <td>
                        @if($item['type'] == 'Fund Disbursed')
                            Credit
                        @elseif(str_contains($item['type'], 'Expense'))
                            Expense
                        @else
                            {{ $item['type'] }}
                        @endif
                    </td>
                    <td style="text-align: left;">{{ $item['description'] ?: '-' }}</td>
                    <td style="font-weight: bold; font-size: 10px;">
                        @if(isset($item['status']))
                            {{ strtoupper($item['status']) }}
                        @else
                            APPROVED
                        @endif
                    </td>
                    <td class="text-right">
                        @if(isset($item['credit']) && $item['credit'] > 0)
                            Tk. {{ number_format($item['credit'], 2) }}
                        @elseif($item['type'] == 'Fund Disbursed')
                             Tk. {{ number_format($item['debit'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right" style="{{ (isset($item['status']) && $item['status'] !== 'approved') ? 'text-decoration: line-through; color: #999;' : '' }}">
                        @if(str_contains($item['type'], 'Expense'))
                            Tk. {{ number_format($item['amount'], 2) }}
                        @elseif($item['type'] == 'Fund Returned by PM')
                            Tk. {{ number_format($item['credit'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total Funds Disbursed to PM:</td>
                <td class="footer-value">Tk. {{ number_format($summary['total_manager_funds'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total Debit (Expenses):</td>
                <td class="footer-value">Tk. {{ number_format($summary['pm_expenses'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total Fund Returned:</td>
                <td class="footer-value">Tk. {{ number_format($summary['total_manager_returns'], 2) }}</td>
            </tr>
            <tr class="footer-row" style="background: #f9f9f9;">
                <td colspan="6" class="footer-label">Manager Hand Cash (Funds - Expenses):</td>
                <td class="footer-value">Tk. {{ number_format($summary['total_manager_funds'] - $summary['pm_expenses'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
