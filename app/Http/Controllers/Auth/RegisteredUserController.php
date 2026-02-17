<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $referralCode = $request->route('referralCode') ?? $request->query('ref');
        $referrerName = $request->query('referrer');

        return Inertia::render('Auth/Register', [
            'referralCode' => $referralCode,
            'referrerName' => $referrerName,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:'.User::class,
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => ['nullable', 'string', 'max:32', 'exists:users,referral_code', 'required_without:referrer_name'],
            'referrer_name' => ['nullable', 'string', 'max:255', 'exists:users,name', 'required_without:referral_code'],
        ]);

        $referrer = null;
        if ($request->filled('referral_code')) {
            $referrer = User::where('referral_code', $request->string('referral_code'))->first();
        } elseif ($request->filled('referrer_name')) {
            $referrer = User::where('name', $request->string('referrer_name'))->first();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'referrer_id' => $referrer?->id,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
