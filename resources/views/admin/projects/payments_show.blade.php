@extends('layouts.app')

@section('title', 'Payment Details: ' . $payment->invoice_no)

@section('content')
    <div class="flex-between" style="margin-bottom: 32px;">
        <div>
            <h2>Payment Details</h2>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="{{ route('admin.projects.payments.invoice', $payment->id) }}" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Print Invoice</a>
            <a href="{{ route('admin.projects.payments.edit', $payment->id) }}" class="btn btn-outline"><i class="fas fa-edit"></i> Edit</a>
            <a href="{{ route('admin.projects.payments.create') }}" class="btn btn-outline">&larr; Back</a>
        </div>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="glass-panel">
            <h3>Transaction Information</h3>
            <table class="table" style="margin-top: 16px;">
                <tr>
                    <td style="color: var(--text-secondary);">Invoice No</td>
                    <td>
                        <a href="{{ route('admin.projects.payments.invoice', $payment->id) }}" target="_blank" style="color: var(--accent-yellow); text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            <strong style="font-family: monospace;">{{ $payment->invoice_no ?? 'N/A' }}</strong>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Project</td>
                    <td><strong>{{ $payment->project->project_name }}</strong></td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Client</td>
                    <td>{{ $payment->project->client_name }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Amount</td>
                    <td style="color: var(--success); font-weight: bold; font-size: 18px;">Tk. {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Date</td>
                    <td>{{ $payment->payment_date->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Method</td>
                    <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                </tr>
            </table>
        </div>

        <div class="glass-panel">
            <h3>System Details</h3>
            <table class="table" style="margin-top: 16px;">
                <tr>
                    <td style="color: var(--text-secondary);">Recorded By</td>
                    <td>{{ $payment->recordedBy->name }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Created At</td>
                    <td>{{ $payment->created_at->format('d M, Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Last Updated</td>
                    <td>{{ $payment->updated_at->format('d M, Y H:i:s') }}</td>
                </tr>
                <tr>
                    <td style="color: var(--text-secondary);">Note</td>
                    <td>{{ $payment->note ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
