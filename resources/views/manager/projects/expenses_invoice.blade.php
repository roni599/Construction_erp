<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Voucher - {{ $expense->invoice_no }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; color: #333; min-height: 100vh; display: flex; flex-direction: column; align-items: center; }
        .invoice-box { width: 100%; max-width: 800px; border: 1px solid #eee; padding: 30px; border-radius: 8px; box-sizing: border-box; background: white; margin-bottom: 80px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #f4f4f4; padding-bottom: 15px; }
        .logo { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .invoice-info { text-align: right; }
        .invoice-info h1 { margin: 0; color: #2c3e50; font-size: 20px; }
        .details { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .details h3 { margin-top: 0; color: #7f8c8d; font-size: 12px; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th { background: #f8f9fa; text-align: left; padding: 10px; border-bottom: 2px solid #eee; font-size: 14px; }
        .table td { padding: 10px; border-bottom: 1px solid #eee; font-size: 14px; }
        .total { text-align: right; font-size: 18px; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; color: #95a5a6; font-size: 11px; border-top: 1px solid #eee; padding-top: 15px; }
        .btn-container { position: fixed; bottom: 30px; left: 0; right: 0; display: flex; justify-content: center; z-index: 1000; pointer-events: none; }
        .no-print-btn { pointer-events: auto; background: #2c3e50; color: white; padding: 12px 30px; border: none; border-radius: 12px; cursor: pointer; font-weight: bold; transition: all 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .no-print-btn:hover { background: #34495e; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.25); }
        @media print { .btn-container { display: none; } body { padding: 0; } .invoice-box { border: none; margin-bottom: 0; } }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="logo">
                @if($adminInfo && $adminInfo->business_logo)
                    <img src="{{ $adminInfo->business_logo }}" alt="Logo" style="max-height: 60px; margin-bottom: 10px;">
                    <div style="font-size: 18px; color: #2c3e50;">{{ $adminInfo->business_name }}</div>
                @else
                    {{ $adminInfo->business_name ?? 'Construction ERP' }}
                @endif

                <div style="font-size: 12px; font-weight: normal; color: #7f8c8d; margin-top: 5px; line-height: 1.4;">
                    @if($adminInfo && $adminInfo->address) <div>{{ $adminInfo->address }}</div> @endif
                    @if($adminInfo && $adminInfo->phone) <div>Phone: {{ $adminInfo->phone }}</div> @endif
                    @if($adminInfo && $adminInfo->email) <div>Email: {{ $adminInfo->email }}</div> @endif
                </div>
            </div>
            <div class="invoice-info">
                <h1>EXPENSE VOUCHER</h1>
                <p>Reference #: <strong>{{ $expense->invoice_no }}</strong></p>
                <p>Date: {{ $expense->expense_date->format('d M, Y') }}</p>
            </div>
        </div>

        <div class="details">
            <div>
                <h3>Expense By</h3>
                <p><strong>{{ $expense->employee->name }}</strong></p>
                <p>Role: Project Manager</p>
                <p>Project: {{ $expense->project->project_name }}</p>
            </div>
            <div>
                <h3>Category & Date</h3>
                <p>Category: {{ $expense->category->name }}</p>
                <p>Expense Date: {{ $expense->expense_date->format('d M, Y') }}</p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $expense->description ?? 'Project Expense' }}</td>
                    <td style="text-align: right;">Tk. {{ number_format($expense->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            Total Spent: Tk. {{ number_format($expense->amount, 2) }}
        </div>

        <div class="footer">
            <p>This is a system-generated expense voucher.</p>
            <p>&copy; {{ date('Y') }} {{ $adminInfo->business_name ?? 'Construction ERP' }}</p>
        </div>
    </div>

    <div class="btn-container">
        <button class="no-print-btn" onclick="window.print()">Print Voucher</button>
    </div>
</body>
</html>
