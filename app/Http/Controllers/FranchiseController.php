<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        // Here you can save to database or send email
        // For now, just send an email to admin
        Mail::raw("New Franchise Application:\n\nName: {$data['name']}\nEmail: {$data['email']}\nPhone: {$data['phone']}\nTarget Location: {$data['target_location']}\nInvestment: \${$data['investment_amount']}\n\nBusiness Plan:\n{$data['business_plan']}", function ($message) use ($data) {
            $message->to('admin@morrisonsph.com')
                    ->subject('New Franchise Application from ' . $data['name']);
        });

        return back()->with('success', 'Franchise application submitted successfully. We will contact you soon.');
    }
}
