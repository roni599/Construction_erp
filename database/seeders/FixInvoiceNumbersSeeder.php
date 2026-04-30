<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientPayment;
use App\Models\ManagerFund;
use App\Models\Expense;
use App\Models\ManagerReturn;

class FixInvoiceNumbersSeeder extends Seeder
{
    public function run()
    {
        // Client Payments
        ClientPayment::all()->each(function($item) {
            $year = $item->payment_date ? $item->payment_date->format('Y') : now()->format('Y');
            $item->update(['invoice_no' => "IN-pay{$year}{$item->id}"]);
        });

        // Manager Funds (Disbursements)
        ManagerFund::all()->each(function($item) {
            $year = $item->fund_date ? $item->fund_date->format('Y') : now()->format('Y');
            $item->update(['invoice_no' => "IN-fund{$year}{$item->id}"]);
        });

        // Expenses
        Expense::all()->each(function($item) {
            $year = $item->expense_date ? $item->expense_date->format('Y') : now()->format('Y');
            $item->update(['invoice_no' => "IN-exp{$year}{$item->id}"]);
        });

        // Manager Returns
        ManagerReturn::all()->each(function($item) {
            $year = $item->return_date ? $item->return_date->format('Y') : now()->format('Y');
            $item->update(['invoice_no' => "IN-ret{$year}{$item->id}"]);
        });
    }
}
