@extends('layouts.app')

@section('title', 'Disbursement Details: ' . $fund->invoice_no)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Disbursement Details</h2>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('admin.projects.funds.invoice', $fund->id) }}" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print Advice</a>
            <a href="{{ route('admin.projects.funds.edit', $fund->id) }}" class="btn btn-outline"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.projects.funds.create') }}" class="btn btn-outline">&larr; Back</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="glass-panel">
            <h3>Disbursement Information</h3>
            <table class="table" style="margin-top: 16px; min-width: auto;">
                <tr>
                    <td style="color: var(--text-secondary);">Invoice Number</td>
                    <td>
                        <a href="{{ route('admin.projects.funds.invoice', $fund->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            <strong style="font-family: monospace;">{{ $fund->invoice_no ?? 'N/A' }}</strong>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Project</td>
                    <td class="text-nowrap"><strong>{{ $fund->project->project_name }}</strong></td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Manager</td>
                    <td class="text-nowrap">{{ $fund->employee->name }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Amount</td>
                    <td style="color: var(--accent-blue); font-weight: bold; font-size: 18px;">Tk. {{ number_format($fund->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Date</td>
                    <td>{{ $fund->fund_date->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Method</td>
                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $fund->payment_method) }}</td>
                </tr>
            </table>
        </div>

        <div class="glass-panel">
            <h3>System Details</h3>
            <table class="table" style="margin-top: 16px; min-width: auto;">
                <tr>
                    <td style="color: var(--text-secondary);">Given By</td>
                    <td>{{ $fund->givenBy->name ?? 'Unknown' }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Created At</td>
                    <td>{{ $fund->created_at->format('d M, Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Last Updated</td>
                    <td>{{ $fund->updated_at->format('d M, Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Note</td>
                    <td>{{ $fund->note ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
