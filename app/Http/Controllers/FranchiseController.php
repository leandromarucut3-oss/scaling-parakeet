<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FranchiseController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'target_location' => ['required', 'string', 'max:255'],
            'business_plan' => ['required', 'string', 'max:5000'],
            'investment_amount' => ['required', 'numeric', 'min:0'],
        ]);

        return back()->with('success', 'Franchise application submitted successfully. We will contact you soon.');
    }
}
