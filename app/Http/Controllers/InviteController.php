<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InviteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $referrals = $user?->referrals()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($referral) => [
                'id' => $referral->id,
                'name' => $referral->name,
                'email' => $referral->email,
                'created_at' => optional($referral->created_at)->toDateString(),
            ]) ?? collect();

        $referralUsername = $user?->name ?? '';
        $referralCode = $user?->referral_code ?? '';
        $referralLink = $referralUsername
            ? route('register.referral', $referralUsername)
            : '';

        return Inertia::render('Invites', [
            'referralLink' => $referralLink,
            'referralUsername' => $referralUsername,
            'referralCode' => $referralCode,
            'referrals' => $referrals,
        ]);
    }
}
