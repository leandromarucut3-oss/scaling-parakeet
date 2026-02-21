<?php

namespace App\Http\Middleware;

use App\Models\Purchase;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $totalInvestmentCents = 0;
        $adminNotifications = null;
        $referralCommissionCents = 0;
        $interestEarnedCents = 0;
        $dailyInterestCents = 0;
        $recentActivity = [];

        if ($user) {
            $totalInvestmentCents = (int) Purchase::query()
                ->where('user_id', $user->id)
                ->sum('amount_cents');

            $referralCommissionCents = (int) Purchase::query()
                ->where('referrer_id', $user->id)
                ->sum('referral_commission_cents');

            $interestEarnedCents = (int) Purchase::query()
                ->where('user_id', $user->id)
                ->sum('interest_earned_cents');

            $dailyInterestCents = (int) Purchase::query()
                ->where('user_id', $user->id)
                ->whereDate('last_interest_at', now()->toDateString())
                ->get(['amount_cents', 'daily_interest_bps'])
                ->sum(function (Purchase $purchase) {
                    return intdiv($purchase->amount_cents * $purchase->daily_interest_bps, 10000);
                });

            $purchaseActivity = Purchase::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn (Purchase $purchase) => [
                    'id' => 'purchase-'.$purchase->id,
                    'type' => 'purchase',
                    'title' => 'Share purchase - '.$purchase->plan_name,
                    'status' => $purchase->status,
                    'amount_cents' => $purchase->amount_cents,
                    'created_at' => optional($purchase->created_at)->toDateTimeString(),
                ]);

            $withdrawalActivity = WithdrawalRequest::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn (WithdrawalRequest $withdrawal) => [
                    'id' => 'withdrawal-'.$withdrawal->id,
                    'type' => 'withdrawal',
                    'title' => 'Withdrawal request',
                    'status' => $withdrawal->status,
                    'amount_cents' => $withdrawal->amount_cents,
                    'created_at' => optional($withdrawal->created_at)->toDateTimeString(),
                ]);

            $interestActivity = Purchase::query()
                ->where('user_id', $user->id)
                ->where('interest_earned_cents', '>', 0)
                ->orderByDesc('last_interest_at')
                ->limit(10)
                ->get()
                ->map(fn (Purchase $purchase) => [
                    'id' => 'interest-'.$purchase->id,
                    'type' => 'interest',
                    'title' => 'Interest accrued - '.$purchase->plan_name,
                    'status' => 'credited',
                    'amount_cents' => $purchase->interest_earned_cents,
                    'created_at' => optional($purchase->last_interest_at)->toDateTimeString(),
                ]);

            $referralActivity = Purchase::query()
                ->where('referrer_id', $user->id)
                ->where('referral_commission_cents', '>', 0)
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn (Purchase $purchase) => [
                    'id' => 'referral-'.$purchase->id,
                    'type' => 'referral',
                    'title' => 'Referral commission',
                    'status' => 'credited',
                    'amount_cents' => $purchase->referral_commission_cents,
                    'created_at' => optional($purchase->created_at)->toDateTimeString(),
                ]);

            $recentActivity = collect($purchaseActivity)
                ->merge($withdrawalActivity)
                ->merge($interestActivity)
                ->merge($referralActivity)
                ->sortByDesc('created_at')
                ->values()
                ->take(6)
                ->all();

            if ($user->hasRole('admin')) {
                $pendingWithdrawals = WithdrawalRequest::query()
                    ->where('status', 'pending')
                    ->count();

                $pendingDeposits = 0;
                if (Schema::hasTable('deposit_requests')) {
                    $pendingDeposits = (int) \DB::table('deposit_requests')
                        ->where('status', 'pending')
                        ->count();
                }

                $adminNotifications = [
                    'pending_withdrawals' => $pendingWithdrawals,
                    'pending_deposits' => $pendingDeposits,
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    ...$user->only(
                        'id',
                        'name',
                        'email',
                        'balance_cents',
                        'bank_name',
                        'bank_account_name',
                        'bank_account_number',
                        'referral_code'
                    ),
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'metrics' => [
                'total_investment_cents' => $totalInvestmentCents,
                'referral_commission_cents' => $referralCommissionCents,
                'interest_earned_cents' => $interestEarnedCents,
                'daily_interest_cents' => $dailyInterestCents,
            ],
            'recent_activity' => $recentActivity,
            'admin_notifications' => $adminNotifications,
            'flash' => [
                'success' => $request->session()->get('success'),
                'purchase_receipt' => $request->session()->get('purchase_receipt'),
                'withdrawal_success' => $request->session()->get('withdrawal_success'),
            ],
        ];
    }
}
