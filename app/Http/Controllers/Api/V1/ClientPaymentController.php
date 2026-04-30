<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ClientPayment;
use App\Models\Project;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function index($projectId)
    {
        $payments = ClientPayment::with('recordedBy')->where('project_id', $projectId)->get();
        return response()->json($payments);
    }

    public function store(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,mobile_banking',
            'reference_no' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ]);

        $payment = ClientPayment::create([
            'project_id' => $project->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_no' => $request->reference_no,
            'note' => $request->note,
            'recorded_by' => $request->user()->id,
        ]);

        return response()->json($payment->load('recordedBy'), 201);
    }
}
