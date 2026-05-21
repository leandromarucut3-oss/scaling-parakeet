<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Withdrawal Confirmed</title>
    <style>
    @media screen and (max-width: 600px) {
      .container { width: 100% !important; }
      .content { padding: 20px !important; }
      .logo-img { width: 180px !important; height: auto !important; }
    }
    </style>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td align="center" style="padding:20px 0">

<table class="container" border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border-top:6px solid #00703c;border-collapse:collapse">

<tbody><tr>
<td align="center" style="padding:30px 40px;background-color:#00703c">

<img src="https://iili.io/BbHjACv.md.png" alt="Morrisons Logo" class="logo-img" style="display:block; max-width:220px; width:100%; height:auto; border:0;">

</td>
</tr>

<tr>
<td class="content" style="padding:40px;color:#333333;line-height:1.6;font-size:16px">

<h2 style="color:#00703c;margin-top:0">Withdrawal Request Processed</h2>

<p>Hi {{ $user->name }},</p>

<p>Good news! Your withdrawal request has been confirmed and successfully processed. The funds are now on their way to your registered account.</p>

<table border="0" cellpadding="10" cellspacing="0" width="100%" style="background-color:#f9f9f9;border:1px solid #eeeeee;margin:20px 0">

<tbody>

<tr>
<td style="font-weight:bold;width:40%">Amount:</td>
<td>$ {{ number_format(($withdrawal->amount_cents ?? 0) / 100, 2) }}</td>
</tr>

<tr>
<td style="font-weight:bold">Account Number:</td>
<td>
@php
    $acct = $withdrawal->bank_account_number ?? '';
    $masked = strlen($acct) > 4 ? str_repeat('*', max(0, strlen($acct)-4)).substr($acct, -4) : $acct;
@endphp
{{ $masked }}
</td>
</tr>

<tr>
<td style="font-weight:bold">Reference:</td>
<td>#MOR-{{ str_pad($withdrawal->id, 8, '0', STR_PAD_LEFT) }}</td>
</tr>

<tr>
<td style="font-weight:bold">Date:</td>
<td>{{ optional($withdrawal->updated_at ?? $withdrawal->created_at)->setTimezone(config('app.timezone'))->format('F j, Y') }}</td>
</tr>

</tbody>
</table>

<p>It usually takes 3–5 working days for the funds to appear in your bank account, depending on your provider.</p>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr>
<td align="center" style="padding:20px 0">

<a href="{{ config('app.url') }}/login" target="_blank" style="background-color:#ffdb00;color:#00703c;padding:15px 30px;text-decoration:none;font-weight:bold;border-radius:4px;display:inline-block;font-size:16px">View My Account</a>

</td>
</tr>
</tbody>
</table>

<p>If you didn't make this request, please contact our support team immediately.</p>

<p>Thanks,<br><strong>The Morrisons Team</strong></p>

</td>
</tr>

<tr>
<td style="padding:20px 40px;background-color:#eeeeee;color:#777777;font-size:12px;text-align:center;line-height:1.5">
<p style="margin:0">© 2026 Wm Morrison Supermarkets Limited. All rights reserved.</p>
</td>
</tr>

</tbody></table>

</td>
</tr>
</tbody>
</table>

</body>
</html>
