<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report - Construction ERP</title>
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
    @php
        $type = $filters['report_type'] ?? 'all';
        $isManager = (auth()->user()->role === 'project_manager');

        if ($isManager) {
            // Manager View: Received from Admin (IN), Expense (OUT), Returned to Admin (OUT)
            $showIn = in_array($type, ['all', 'fund_pm']);
            $showOut = in_array($type, ['all', 'expense', 'fund_return']);
        } else {
            // Admin View: Client Payment (IN), Fund Return (IN), Disbursed to PM (OUT), Expense (OUT)
            $showIn = in_array($type, ['all', 'client_received', 'fund_return']);
            $showOut = in_array($type, ['all', 'fund_pm', 'expense']);
        }

        $showStatus = in_array($type, ['all', 'expense']);
        $colCount = 6 + ($showStatus ? 1 : 0) + ($showIn ? 1 : 0) + ($showOut ? 1 : 0);
    @endphp

    @php
        $typeLabels = [
            'all' => 'Financial Report',
            'fund_pm' => 'Funds Received Report',
            'expense' => 'Expenses Report',
            'fund_return' => 'Funds Returned Report',
            'client_received' => 'Client Payments Report'
        ];
        $titleText = $typeLabels[$type] ?? 'Financial Report';
    @endphp

    <table class="report-table">
        <thead>
            <tr>
                <th colspan="{{ $colCount }}" style="background: #fff; color: #000; font-size: 18px; padding: 10px; border: 1px solid #000;">{{ $titleText }}</th>
            </tr>
            <tr>
                <th colspan="{{ $colCount }}" style="padding: 8px; border: 1px solid #000; font-weight: bold; background: white; color: black; text-align: center;">
                    Project: {{ $project ? $project->project_name : 'All Projects' }}
                </th>
            </tr>
            @if(isset($filters['from_date']) || isset($filters['to_date']))
            <tr>
                <th colspan="{{ $colCount }}" style="padding: 8px; border: 1px solid #000; background: white; color: black; text-align: center; font-weight: normal;">
                    Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}
                </th>
            </tr>
            @endif
            <tr>
                <th colspan="{{ $colCount }}" style="padding: 8px; border: 1px solid #000; background: white; color: black; text-align: center; font-weight: normal;">
                    Print Date: {{ date('Y-m-d') }}
                </th>
            </tr>
            <tr>
                <th style="width: 8%;">Date</th>
                <th style="width: 8%;">Invoice</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 8%;">Method</th>
                <th style="width: 10%;">Category</th>
                <th style="width: 18%;">Description</th>
                @if($showStatus) <th style="width: 8%;">Status</th> @endif
                @if($showIn) <th style="width: 15%; text-align: center;">{{ $type === 'fund_pm' && $isManager ? 'Fund Received' : ($type === 'fund_return' && !$isManager ? 'Fund Return' : 'Received (In)') }}</th> @endif
                @if($showOut) <th style="width: 15%; text-align: center;">{{ $type === 'fund_return' && $isManager ? 'Fund Return' : ($type === 'fund_pm' && !$isManager ? 'Fund Disbursed' : 'Spent (Out)') }}</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($report_data as $item)
                <tr>
                    <td>{{ $item['date']->format('Y-m-d') }}</td>
                    <td>
                        <a href="{{ $item['invoice_url'] }}" target="_blank" style="color: #000; text-decoration: none; font-weight: 600;">
                            {{ $item['invoice_no'] }}
                        </a>
                    </td>
                    <td>{{ $item['type'] }}</td>
                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $item['method']) }}</td>
                    <td>{{ $item['category'] }}</td>
                    <td style="text-align: left; padding-left: 5px;">{{ $item['description'] ?: '-' }}</td>
                    @if($showStatus)
                    <td style="font-weight: bold; font-size: 10px;">
                        @if(isset($item['status']))
                            {{ strtoupper($item['status']) }}
                        @else
                            APPROVED
                        @endif
                    </td>
                    @endif
                    @if($showIn) 
                        <td class="text-right">{{ $item['credit'] > 0 ? 'Tk. ' . number_format($item['credit'], 2) : '-' }}</td> 
                    @endif
                    @if($showOut) 
                        <td class="text-right" style="{{ (isset($item['status']) && $item['status'] !== 'approved') ? 'text-decoration: line-through; color: #999;' : '' }}">
                            @if($item['type'] === 'Expense')
                                Tk. {{ number_format($item['amount'], 2) }}
                            @else
                                {{ $item['debit'] > 0 ? 'Tk. ' . number_format($item['debit'], 2) : '-' }}
                            @endif
                        </td> 
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if(!$isManager)
            @if(in_array($type, ['all', 'client_received']))
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">Total Received from Client:</td>
                <td class="footer-value">Tk. {{ number_format($totals['client_received'], 2) }}</td>
            </tr>
            @endif
            @if(in_array($type, ['all', 'fund_pm']))
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">Total Transferred to PM:</td>
                <td class="footer-value">Tk. {{ number_format($totals['transferred_pm'], 2) }}</td>
            </tr>
            @endif
            @if(in_array($type, ['all', 'expense']))
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">Total PM Expenses:</td>
                <td class="footer-value">Tk. {{ number_format($totals['pm_expenses'] ?? 0, 2) }}</td>
            </tr>
            @endif
            @if(in_array($type, ['all', 'fund_return']))
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">Total Fund Returned by PM:</td>
                <td class="footer-value">Tk. {{ number_format($totals['fund_returned'] ?? 0, 2) }}</td>
            </tr>
            @endif
            @if($type === 'all')
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">Office Balance (Received - Transferred):</td>
                <td class="footer-value">Tk. {{ number_format($totals['office_balance'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="{{ $colCount - 1 }}" class="footer-label">PM Hand Cash (Transferred - Expenses):</td>
                <td class="footer-value">Tk. {{ number_format($totals['pm_hand_cash'], 2) }}</td>
            </tr>
            @endif
            @else
            @php
                $totalIn = $report_data->sum('credit');
                $totalOut = $report_data->sum('debit');
            @endphp
            <tr class="footer-row">
                <td colspan="{{ $colCount - ($showIn && $showOut ? 2 : 1) }}" class="footer-label">Total:</td>
                @if($showIn) <td class="footer-value">Tk. {{ number_format($totalIn, 2) }}</td> @endif
                @if($showOut) <td class="footer-value">Tk. {{ number_format($totalOut, 2) }}</td> @endif
            </tr>
            @endif
        </tfoot>
    </table>

    <div class="no-print" style="margin-top: 50px; text-align: center; padding-bottom: 50px;">
        <button onclick="window.print()" style="background: #27ae60; color: white; border: none; padding: 14px 32px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 18px; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); display: inline-flex; align-items: center; gap: 10px;">
            <i class="fas fa-print"></i> Print Now
        </button>
    </div>
</body>
</html>
