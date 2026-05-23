<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MORRISONS Philippines Investment Partnership Agreement</title>

<style>
    @page {
        size: A4;
        margin: 0.8in;
    }

    body{
        font-family:"Times New Roman", serif;
        font-size:14px;
        line-height:1.45;
        color:#000;
        margin:0;
    }

    h1,h2,h3{
        text-align:center;
        margin:4px 0;
    }

    p{
        margin:8px 0;
    }

    .justify{
        text-align:justify;
    }

    .fillable{
        display:inline-block;
        border-bottom:1px solid #000;
        min-width:180px;
        padding:2px 4px;
    }

    .long-fill{
        min-width:350px;
    }

    .section{
        margin-top:18px;
    }

    .page-break{
        page-break-before:always;
    }

    .signature-container{
        margin-top:50px;
        width:100%;
    }

    .signature-box{
        width:45%;
        display:inline-block;
        vertical-align:top;
        text-align:center;
    }

    .signature-line{
        border-top:1px solid #000;
        margin-top:60px;
        padding-top:5px;
    }

    table{
        width:100%;
        border-collapse:collapse;
        margin-top:15px;
    }

    table th, table td{
        border:1px solid #000;
        padding:8px;
        text-align:left;
    }

    .small-space{
        height:20px;
    }
</style>

</head>
<body>

@php
    $displayDate = ($contractDate ?? $purchase->updated_at ?? $purchase->created_at ?? $contract->signed_at ?? now())
        ->copy()
        ->setTimezone(config('app.timezone'));
@endphp

<!-- PAGE 1 -->

<h2>MORRISONS PHILIPPINES</h2>
<h3>INVESTMENT PARTNERSHIP AGREEMENT</h3>

<p class="justify">
This Partnership Agreement (the "Agreement") is executed on
<span class="fillable">{{ $displayDate->format('F j, Y') }}</span>
by and between:
</p>

<p class="justify">
<strong>MORRISONS</strong>, a duly organized and existing corporation under the laws of the Republic of the Philippines, with principal business address at 18/20 Upper McKinley Bldg, McKinley Hill Taguig City, Philippines, 1634, represented herein by its Finance Officer, Mr. Jubert Undaya Yacup, hereinafter referred to as the "Company";
</p>

<p class="justify">
<span class="fillable long-fill">{{ $contract->investor_name }}</span>
of legal age, Filipina,
<span class="fillable">{{ $contract->civil_status }}</span>,
and a resident of
<span class="fillable long-fill">{{ $contract->complete_address }}</span>,
Philippines hereinafter referred to as the "Investor" or "Partner."
</p>

<h3>WITNESSETH:</h3>

<p class="justify">
WHEREAS, the Company is engaged in providing investment opportunities through its Partnership Investment Program, which allows qualified investors to participate in its business operations by investing in specified plans with guaranteed daily interest returns;
</p>

<p class="justify">
WHEREAS, the Company guarantees payment of daily interest income on investments, irrespective of prevailing economic conditions, market performance, or other external circumstances;
</p>

<p class="justify">
WHEREAS, the investor has expressed intent to participate in the Company's Co-Partnership Program by availing of the Premiere Plan, subject to the terms and conditions set forth herein;
</p>

<p class="justify">
NOW, THEREFORE, for and in consideration of the foregoing premises and the mutual covenants herein contained, the parties hereby agree as follows:
</p>

@php
    $usdAmount = $usdAmount ?? ($purchase->amount_cents / 100);
    $phpAmount = $phpAmount ?? ($usdAmount * $usdToPhpRate);
    $dailyInterestUsd = $dailyInterestUsd ?? ($usdAmount * ($purchase->daily_interest_bps / 10000));
    $dailyInterestPhp = $dailyInterestPhp ?? ($dailyInterestUsd * $usdToPhpRate);
endphp

<div class="section">
<h3>1. INVESTMENT PACKAGE</h3>

<p>Plan Type:
<span class="fillable">{{ $purchase->plan_name ?? 'Premiere Plan' }}</span></p>

<p>Contract Term:
<span class="fillable">{{ $purchase->duration_days ?? '' }}</span>
days</p>

<p>Investment Amount:
<span class="fillable">$ {{ number_format($usdAmount, 2) }}</span></p>

<p>Daily Interest Rate:
<span class="fillable">{{ number_format($purchase->daily_interest_bps / 100, 2) }}%</span></p>

<p>Commencement Date:
<span class="fillable">{{ $displayDate->format('F j, Y') }}</span></p>
</div>

<div class="section">
<h3>2. TERM AND PAYMENT OF INTEREST</h3>

<p class="justify">
The Company shall pay the Investor a daily interest income based on the selected plan and investment amount, computed at $
<span class="fillable">{{ number_format($dailyInterestUsd, 2) }}</span>
per day.
</p>

<p class="justify">
Interest shall be credited daily to the Investor's designated bank account.
</p>

<p class="justify">
Account balances reflected on the Company's dashboard system shall be withdrawable to affiliated bank accounts, subject to a 5% processing fee.
</p>

<div class="page-break"></div>

<!-- PAGE 2 -->

<h3>2. TERM AND PAYMENT OF INTEREST (CONTINUED)</h3>

<p class="justify">
Upon maturity of the term, the Investor's full capital shall be credited back to the Investor's dashboard account. Said capital may be withdrawn or reinvested into a new 120-days plan, subject to availability of slots.
</p>

<p class="justify">
The Partnership Program shall remain open and renewable until June 2030.
</p>

<div class="section">
<h3>3. WITHDRAWAL OF CAPITAL</h3>

<p class="justify">
The Investor may request withdrawal of capital upon contract maturity. Released amounts shall be credited within three (3) banking days from the date of request.
</p>
</div>

<div class="section">
<h3>4. EARLY WITHDRAWAL OF CAPITAL</h3>

<p class="justify">
Should the Investor request an early withdrawal of capital prior to maturity, a ten percent (10%) penalty fee shall apply. Capital withdrawals shall be processed and released within three (3) banking days from the date of request.
</p>
</div>

<div class="section">
<h3>5. CONFIDENTIALITY</h3>

<p class="justify">
Both the Company and the Investor agree to treat as confidential all business information, records, financial data, strategies, and other proprietary information acquired in relation to this Agreement.
</p>

<p class="justify">
Disclosure of any such information to third parties shall be strictly prohibited unless required by law, regulatory authorities, or upon written consent of the other party.
</p>

<p class="justify">
This confidentiality obligation shall survive the termination or expiration of this Agreement.
</p>
</div>

<div class="section">
<h3>6. NON-TRANSFERABILITY</h3>

<p class="justify">
The rights and obligations under this Agreement are personal to the Investor and may not be assigned, transferred, or sold to any third party without the prior written consent of the Company. Any unauthorized transfer shall be deemed null and void.
</p>
</div>

<div class="section">
<h3>7. DISPUTE RESOLUTION</h3>

<p><strong>Amicable Settlement –</strong> The parties shall first attempt to resolve the matter amicably through good faith negotiation within thirty (30) days from written notice of the dispute.</p>

<p><strong>Mediation –</strong> If unresolved, the parties shall submit the matter to mediation under the rules of the Philippine Dispute Resolution Center Inc. (PDRCI) or any recognized mediation body.</p>

<div class="page-break"></div>

<!-- PAGE 3 -->

<h3>7. DISPUTE RESOLUTION (CONTINUED)</h3>

<p><strong>Arbitration –</strong> Should mediation fail, the dispute shall be finally resolved by arbitration in Taguig City, Philippines, in accordance with the Arbitration Law of the Philippines (R.A. 876) and the Alternative Dispute Resolution Act of 2004 (R.A. 9285). The arbitral award shall be final and binding upon both parties.</p>

<p><strong>Court Action –</strong> Only when arbitration is not feasible may the parties seek relief from the proper courts of Taguig City, Philippines, to the exclusion of all other venues.</p>

<div class="section">
<h3>8. GOVERNING LAW</h3>

<p class="justify">
This Agreement shall be governed by and construed in accordance with the laws of the Republic of the Philippines, particularly the relevant provisions of the Securities Regulation Code and other applicable laws.
</p>
</div>

<div class="section">
<h3>9. ENTIRE AGREEMENT</h3>

<p class="justify">
This Agreement constitutes the entire understanding between the parties and supersedes all prior agreements, representations, or arrangements, whether oral or written, relating to the subject matter herein.
</p>
</div>

<br><br>

<h3>IN WITNESS WHEREOF,</h3>

<p class="justify">
The parties hereunto affixed their signatures at Taguig City, Philippines.
</p>

<div class="signature-container">

    <div class="signature-box">
        <div class="signature-line">
            Jubert Undaya Yacup
        </div>
        Morrisons Finance Officer
    </div>

    <div class="signature-box" style="float:right;">
        <div class="signature-line">
            {{ $contract->signature_text }}
        </div>
        Investor
    </div>

</div>

<div style="clear:both;"></div>

<br><br><br>

<h3>SIGNED IN THE PRESENCE OF:</h3>

<div class="signature-container">

    <div class="signature-box">
        <div class="signature-line">
            Sheena Mae Polentes
        </div>
        Morrisons Marketing Officer
    </div>

    <div class="signature-box" style="float:right;">
        <div class="signature-line">
            Dennis Mendoza
        </div>
        Documentation Officer
    </div>

</div>

<div class="page-break"></div>

<!-- PAGE 4 -->

<h3>ACKNOWLEDGMENT</h3>

<p>
Republic of the Philippines<br>
City of Taguig S.S.
</p>

<p class="justify">
BEFORE ME, a Notary Public for and in the City of Taguig, personally appeared the following persons:
</p>

<table>
<tr>
    <th>Name</th>
    <th>ID Type</th>
    <th>ID No.</th>
    <th>Date Issued</th>
</tr>

<tr>
    <td>{{ $contract->investor_name }}</td>
    <td>{{ $contract->id_type }}</td>
    <td>{{ $contract->id_number }}</td>
    <td>{{ $contract->id_date_issued }}</td>
</tr>

<tr>
    <td>Jubert Undaya Yacup</td>
    <td></td>
    <td></td>
    <td></td>
</tr>
</table>

<p class="justify">
Known to me and identified by competent evidence of identity, and who acknowledged to me that the foregoing instrument is their free and voluntary act and deed, and that of the entity they respectively represent.
</p>

<p class="justify">
This Agreement consists of four (4) pages, including the page on which this acknowledgment is written, duly signed by the parties and their witnesses on each page.
</p>

<p class="justify">
The parties hereunto affixed their signatures this
<span class="fillable">{{ $displayDate->format('F j, Y') }}</span>
at Taguig City, Philippines.
</p>

<p class="justify">
IN WITNESS WHEREOF, I have hereunto set my hand and affixed my notarial seal on the date and at the place above written.
</p>

<br><br><br>

<div class="signature-line" style="width:300px;">
    Notary Public
</div>

<br><br>

<p>
Doc. No. _______;<br><br>
Page No. _______;<br><br>
Book No. _______;<br><br>
Series of {{ $displayDate->format('Y') }}
</p>

</body>
</html>
