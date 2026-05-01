<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report - Construction ERP</title>
    <style>
        body { font-family: 'Inter', -apple-system, sans-serif; padding: 40px; color: #333; line-height: 1.6; background: #fff; }
        .header { text-align: center; margin-bottom: 30px; border: 2px solid #000; padding: 15px; border-radius: 8px; }
        .header h1 { margin: 0; color: #000; font-size: 21px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { margin: 5px 0; color: #333; font-size: 16px; font-weight: 600; }
        .header p { margin: 2px 0; color: #555; font-size: 13px; }
        
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        .report-table th { background-color: white; color: black; border: 1px solid #ddd; padding: 10px 5px; text-align: center; font-weight: bold; }
        .report-table td { border: 1px solid #ddd; padding: 8px 5px; text-align: center; }
        .report-table .text-right { text-align: right; padding-right: 10px; }
        .report-table tr:nth-child(even) { background-color: #fcfcfc; }
        
        .footer-row td { font-weight: bold; background-color: #f2f2f2 !important; border: 1px solid #ddd !important; padding: 10px !important; }
        .footer-label { text-align: right !important; }
        .footer-value { text-align: right !important; color: #000; }
        
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .report-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: white !important; color: black !important; }
            .footer-row td { -webkit-print-color-adjust: exact; print-color-adjust: exact; background-color: #f2f2f2 !important; }
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

        $colCount = 6 + ($showIn ? 1 : 0) + ($showOut ? 1 : 0);
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

    <table class="report-table" style="margin-bottom: 0; border-bottom: none;">
        <tbody>
            <tr>
                <th style="background-color: #f8f9fa; color: #000; font-size: 20px; padding: 12px; text-transform: uppercase; border: 1px solid #000;">{{ $titleText }}</th>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #000; font-weight: bold;">
                    Project: {{ $project ? $project->project_name : 'All Projects' }}
                </td>
            </tr>
            @if(isset($filters['from_date']) || isset($filters['to_date']))
            <tr>
                <td style="padding: 8px; border: 1px solid #000;">Period: {{ $filters['from_date'] ?: 'Start' }} to {{ $filters['to_date'] ?: 'End' }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px; border: 1px solid #000;">Print Date: {{ date('Y-m-d') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="report-table" style="margin-top: 0;">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 10%;">Invoice</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 12%;">Method</th>
                <th style="width: 15%;">Category</th>
                <th>Description</th>
                @if($showIn) <th style="width: 15%;" class="text-right">{{ $type === 'fund_pm' && $isManager ? 'Fund Received' : ($type === 'fund_return' && !$isManager ? 'Fund Return' : 'Received (In)') }}</th> @endif
                @if($showOut) <th style="width: 15%;" class="text-right">{{ $type === 'fund_return' && $isManager ? 'Fund Return' : ($type === 'fund_pm' && !$isManager ? 'Fund Disbursed' : 'Spent (Out)') }}</th> @endif
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
                    <td style="text-align: left; padding-left: 10px;">{{ $item['description'] }}</td>
                    @if($showIn) <td class="text-right">{{ $item['credit'] > 0 ? 'Tk. ' . number_format($item['credit'], 2) : '-' }}</td> @endif
                    @if($showOut) <td class="text-right">{{ $item['debit'] > 0 ? 'Tk. ' . number_format($item['debit'], 2) : '-' }}</td> @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if(!$isManager)
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total Received from Client:</td>
                <td colspan="{{ $colCount - 6 }}" class="footer-value">Tk. {{ number_format($totals['client_received'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total Transferred to PM:</td>
                <td colspan="{{ $colCount - 6 }}" class="footer-value">Tk. {{ number_format($totals['transferred_pm'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total PM Expenses:</td>
                <td colspan="{{ $colCount - 6 }}" class="footer-value">Tk. {{ number_format($totals['pm_expenses'] ?? 0, 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Office Balance (Received - Transferred):</td>
                <td colspan="{{ $colCount - 6 }}" class="footer-value">Tk. {{ number_format($totals['office_balance'], 2) }}</td>
            </tr>
            <tr class="footer-row">
                <td colspan="6" class="footer-label">PM Hand Cash (Transferred - Expenses):</td>
                <td colspan="{{ $colCount - 6 }}" class="footer-value">Tk. {{ number_format($totals['pm_hand_cash'], 2) }}</td>
            </tr>
            @else
            @php
                $totalIn = $report_data->sum('credit');
                $totalOut = $report_data->sum('debit');
            @endphp
            <tr class="footer-row">
                <td colspan="6" class="footer-label">Total:</td>
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
