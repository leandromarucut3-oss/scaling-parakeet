<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Transfer;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Carbon;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $deposits = Purchase::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Purchase $purchase) {
                $elapsedDays = $purchase->created_at
                    ? Carbon::now()->diffInDays($purchase->created_at)
                    : null;
                $durationDays = $purchase->duration_days;
                $remainingDays = $durationDays !== null && $elapsedDays !== null
                    ? max((int) $durationDays - (int) $elapsedDays, 0)
                    : null;

                return [
                    'id' => $purchase->id,
                    'plan_name' => $purchase->plan_name,
                    'amount_cents' => $purchase->amount_cents,
                    'payment_method' => $purchase->payment_method,
                    'status' => $purchase->status,
                    'created_at' => optional($purchase->created_at)->toDateTimeString(),
                    'remaining_days' => $remainingDays,
                ];
            });

        $withdrawals = WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (WithdrawalRequest $withdrawal) => [
                'id' => $withdrawal->id,
                'amount_cents' => $withdrawal->amount_cents,
                'status' => $withdrawal->status,
                'created_at' => optional($withdrawal->created_at)->toDateTimeString(),
            ]);

        $interest = Purchase::query()
            ->where('user_id', $user->id)
            ->where('interest_earned_cents', '>', 0)
            ->orderByDesc('last_interest_at')
            ->get()
            ->map(fn (Purchase $purchase) => [
                'id' => $purchase->id,
                'plan_name' => $purchase->plan_name,
                'amount_cents' => $purchase->interest_earned_cents,
                'created_at' => optional($purchase->last_interest_at)->toDateTimeString(),
            ]);

        $transfers = Transfer::query()
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Transfer $transfer) use ($user) {
                $isSender = $transfer->sender_id === $user->id;
                $counterparty = $isSender
                    ? optional($transfer->recipient)->email
                    : optional($transfer->sender)->email;

                return [
                    'id' => $transfer->id,
                    'direction' => $isSender ? 'sent' : 'received',
                    'counterparty' => $counterparty,
                    'amount_cents' => $transfer->amount_cents,
                    'status' => 'completed',
                    'created_at' => optional($transfer->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Statements', [
            'deposits' => $deposits,
            'withdrawals' => $withdrawals,
            'interest' => $interest,
            'transfers' => $transfers,
        ]);
    }
}
