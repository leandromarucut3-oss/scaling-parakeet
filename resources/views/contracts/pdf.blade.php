<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Contract</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #222; line-height: 1.6; }
        .page { width: 100%; margin: 0 auto; padding: 24px; }
        .logo { text-align: center; margin-bottom: 24px; }
        .logo h1 { margin: 0; font-size: 24px; color: #006b3c; }
        .title { text-align: center; margin-top: 4px; margin-bottom: 20px; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; color: #444; }
        .section-title { margin-top: 22px; margin-bottom: 10px; font-weight: 700; color: #006b3c; text-transform: uppercase; letter-spacing: 1px; font-size: 13px; }
        .info-box { padding: 14px; border: 1px solid #e5e5e5; background: #fafafa; border-radius: 8px; }
        .field-label { font-weight: 700; }
        .field-value { margin-bottom: 8px; }
        .signature-block { margin-top: 40px; }
        .signature-line { margin-top: 18px; border-top: 1px solid #000; padding-top: 8px; font-size: 11px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; font-size: 11px; }
        .table th { background: #006b3c; color: #fff; }
    </style>
</head>
<body>
    <div class="page">
        <div class="logo">
            <h1>MORRISONS GLOBAL</h1>
            <div class="title">Investment Partnership Agreement</div>
        </div>

        <p>This Investment Partnership Agreement is entered into on <strong>{{ optional($contract->signed_at)->format('F j, Y') ?? now()->format('F j, Y') }}</strong> by and between:</p>

        <div class="info-box">
            <p><strong>MORRISONS GLOBAL</strong>, a duly organized and existing entity operating under the applicable laws of the Republic of the Philippines, with principal office located at 18/20 Upper McKinley Building, McKinley Hill, Taguig City, Philippines, represented herein by its authorized Finance Officer, <strong>Mr. Jubert Undaya Yacup</strong>, hereinafter referred to as the <strong>“Company”</strong>.</p>

            <p><strong>AND</strong></p>

            <p><strong>{{ $contract->investor_name }}</strong>, of legal age, <strong>{{ $contract->civil_status }}</strong>, residing at <strong>{{ $contract->complete_address }}</strong>, Philippines, hereinafter referred to as the <strong>“Investor”</strong>.</p>
        </div>

        <div class="section-title">Investment Package</div>
        <div class="info-box">
            <p class="field-value"><span class="field-label">Plan Type:</span> {{ $purchase->plan_name }}</p>
            <p class="field-value"><span class="field-label">Contract Term:</span> {{ $purchase->duration_days }} days</p>
            <p class="field-value"><span class="field-label">Investment Amount:</span> {{ number_format($purchase->amount_cents / 100, 2) }}</p>
            <p class="field-value"><span class="field-label">Daily Interest Rate:</span> {{ number_format($purchase->daily_interest_bps / 100, 2) }}%</p>
        </div>

        <div class="section-title">Terms and Conditions</div>
        <p>The Company agrees to provide the Investor with daily earnings or interest computed according to the agreed investment package and prevailing program terms. Interest earnings shall be credited daily to the Investor’s registered account.</p>
        <p>Upon completion of the agreed contract term of {{ $purchase->duration_days }} days, the Investor shall be entitled to receive the full return of capital subject to verification and processing requirements.</p>
        <p>The Investor may request withdrawal of capital upon maturity of the investment contract, subject to the Company’s standard processing procedures.</p>
        <p>Early withdrawal prior to contract maturity may be subject to company policies and fees as provided in the investment agreement.</p>

        <div class="section-title">Investor Information</div>
        <table class="table">
            <tr>
                <th>Investor Name</th>
                <td>{{ $contract->investor_name }}</td>
            </tr>
            <tr>
                <th>Civil Status</th>
                <td>{{ $contract->civil_status }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $contract->complete_address }}</td>
            </tr>
            <tr>
                <th>ID Type</th>
                <td>{{ $contract->id_type }}</td>
            </tr>
            <tr>
                <th>ID Number</th>
                <td>{{ $contract->id_number }}</td>
            </tr>
            <tr>
                <th>Date Issued</th>
                <td>{{ $contract->id_date_issued }}</td>
            </tr>
        </table>

        <div class="signature-block">
            <div class="field-label">Investor Signature</div>
            <p>{{ $contract->signature_text }}</p>
            <div class="signature-line">Investor Signature</div>
        </div>

        <div class="signature-block">
            <div class="field-label">Finance Officer</div>
            <p>Jubert Undaya Yacup</p>
            <div class="signature-line">Finance Officer, MORRISONS GLOBAL</div>
        </div>
    </div>
</body>
</html>
