<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransferController extends Controller
{
    public function index()
    {
        return Inertia::render('Transfer');
    }

    public function store(Request $request)
    {
        try {
            $sender = $request->user();

            $data = $request->validate([
                'recipient_email' => ['required', 'email', 'exists:users,email'],
                'amount' => ['required', 'numeric', 'min:0.01'],
            ]);

            $recipient = User::where('email', $data['recipient_email'])->first();
            $amountCents = (int) round($data['amount'] * 100);

            if ($sender->id === $recipient->id) {
                throw ValidationException::withMessages([
                    'recipient_email' => 'You cannot transfer funds to yourself.',
                ]);
            }

            if ($sender->balance_cents < $amountCents) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance.',
                ]);
            }

            $receiptTransfer = null;
            $now = now();

            DB::transaction(function () use ($sender, $recipient, $amountCents, &$receiptTransfer, $now) {
                $senderLocked = User::query()->whereKey($sender->id)->lockForUpdate()->first();
                $recipientLocked = User::query()->whereKey($recipient->id)->lockForUpdate()->first();

                $senderLocked->balance_cents -= $amountCents;
                $recipientLocked->balance_cents += $amountCents;

                $senderLocked->save();
                $recipientLocked->save();

                $receiptTransfer = Transfer::create([
                    'sender_id' => $senderLocked->id,
                    'recipient_id' => $recipientLocked->id,
                    'amount_cents' => $amountCents,
                    'status' => 'completed',
                ]);
            });

            return redirect()->route('transfer.index')->with([
                'success' => 'Funds transferred successfully.',
                'transfer_receipt' => [
                    'amount_cents' => $amountCents,
                    'sender_name' => $sender->name,
                    'sender_business' => 'Morrisons Commercial & General Merchandise Co.',
                    'recipient_name' => $recipient->name ?: $recipient->email,
                    'recipient_method' => 'Morrisons wallet',
                    'reference_number' => sprintf('MRC-FT-%s-%s', $now->format('Ymd'), $receiptTransfer->id),
                    'transaction_date' => $now->format('F j, Y • g:i A'),
                    'destination_account' => $recipient->email,
                    'processing_fee' => 0,
                    'status' => 'Completed',
                    'remarks' => 'Payment successfully transferred and verified through the Morrisons secure transaction system.',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Transfer failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
