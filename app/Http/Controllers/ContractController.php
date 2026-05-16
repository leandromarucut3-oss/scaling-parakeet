<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Purchase;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $contract = Contract::firstWhere('user_id', $user->id);
        $purchase = null;

        if (! $contract && $request->query('purchase_id')) {
            $purchase = Purchase::where('id', $request->query('purchase_id'))
                ->where('user_id', $user->id)
                ->first();
        }

        return Inertia::render('ContractForm', [
            'contract' => $contract,
            'purchase' => $purchase,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'investor_name' => ['required', 'string', 'max:255'],
            'civil_status' => ['nullable', 'string', 'max:255'],
            'complete_address' => ['nullable', 'string', 'max:1000'],
            'id_type' => ['nullable', 'string', 'max:255'],
            'id_number' => ['nullable', 'string', 'max:255'],
            'id_date_issued' => ['nullable', 'string', 'max:255'],
            'signature_text' => ['required', 'string', 'max:255'],
            'signed_at' => ['required', 'date'],
            'purchase_id' => ['nullable', 'integer', 'exists:purchases,id'],
        ]);

        $contract = Contract::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'investor_name' => $data['investor_name'],
            'civil_status' => $data['civil_status'],
            'complete_address' => $data['complete_address'],
            'id_type' => $data['id_type'],
            'id_number' => $data['id_number'],
            'id_date_issued' => $data['id_date_issued'],
            'signature_text' => $data['signature_text'],
            'signed_at' => $data['signed_at'],
        ]);

        if (! empty($data['purchase_id'])) {
            $purchase = Purchase::where('id', $data['purchase_id'])
                ->where('user_id', $user->id)
                ->first();

            if ($purchase) {
                ContractService::sendPurchaseContract($user, $purchase, $contract);
            }
        }

        return Redirect::route('dashboard')
            ->with('success', 'Contract submitted successfully. A PDF copy has been sent to your email.');
    }
}
