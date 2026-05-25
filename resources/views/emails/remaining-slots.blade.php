<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Secure Your Slot Today</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body style="margin:0; padding:0; background-color:#f3f5f2; font-family:Arial, Helvetica, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f3f5f2; margin:0; padding:0;">
    <tr>
      <td align="center" style="padding:30px 12px;">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#ffffff; border:1px solid #dfe6df;">
          <tr>
            <td align="center" style="background-color:#004e37; padding:28px 30px;">
              <div style="font-size:30px; line-height:34px; font-weight:bold; color:#ffffff; letter-spacing:0.5px;">Morrisons</div>
              <div style="font-size:13px; line-height:20px; color:#d9eadf; margin-top:5px;">Since 1899</div>
            </td>
          </tr>

          <tr>
            <td align="center" style="background-color:#d4af37; padding:11px 20px;">
              <div style="font-size:14px; line-height:20px; color:#003d2a; font-weight:bold; letter-spacing:0.4px;">LIMITED SLOTS AVAILABLE FOR ALL PACKAGES</div>
            </td>
          </tr>

          <tr>
            <td align="center" style="padding:28px 30px 10px 30px; background-color:#ffffff;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #dfe6df; background-color:#f8faf8;">
                <tr>
                  <td align="center" style="padding:22px 18px;">
                    <img src="{{ $slotsImageUrl }}"
                         alt="Secure Your Slot Today - Morrisons Packages"
                         width="540"
                         style="display:block; width:100%; max-width:540px; height:auto; border:0; outline:none; text-decoration:none;">
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:26px 36px 12px 36px; color:#2b2b2b;">
              <h1 style="margin:0 0 14px 0; font-size:26px; line-height:34px; color:#004e37; font-weight:bold;">Secure Your Preferred Package Today</h1>

              <p style="margin:0 0 16px 0; font-size:15px; line-height:24px; color:#444444;">Dear {{ $user->name ?? 'Valued Client' }},</p>

              <p style="margin:0 0 18px 0; font-size:15px; line-height:24px; color:#444444;">We would like to inform you that only a few slots remain available for selected Morrisons packages. Due to continued demand, we encourage interested clients to secure their preferred package while availability is still open.</p>

              <p style="margin:0 0 22px 0; font-size:15px; line-height:24px; color:#444444;">Reservations and activations will be accommodated on a first-come, first-served basis.</p>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse; margin:0 0 26px 0;">
                <tr>
                  <td colspan="2" style="background-color:#004e37; padding:12px 16px; font-size:15px; line-height:20px; color:#ffffff; font-weight:bold;">Current Slot Availability</td>
                </tr>

                @foreach ($packages as $package)
                  <tr>
                    <td style="border-left:1px solid #dfe6df; border-bottom:1px solid #dfe6df; padding:14px 16px; font-size:15px; color:#004e37; font-weight:bold;">
                      {{ $package['name'] }} Plan
                    </td>
                    <td align="right" style="border-right:1px solid #dfe6df; border-bottom:1px solid #dfe6df; padding:14px 16px; font-size:15px; color:#a00000; font-weight:bold;">
                      {{ number_format($package['remaining_slots']) }} {{ \Illuminate\Support\Str::plural('Slot', $package['remaining_slots']) }} Left
                    </td>
                  </tr>
                @endforeach
              </table>

              <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto 24px auto;">
                <tr>
                  <td align="center" bgcolor="#004e37" style="background-color:#004e37;">
                    <a href="{{ $ctaUrl }}" target="_blank" style="display:inline-block; padding:15px 34px; font-size:15px; line-height:20px; font-weight:bold; color:#ffffff; text-decoration:none; letter-spacing:0.3px;">SECURE YOUR SLOT NOW</a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 16px 0; font-size:14px; line-height:22px; color:#555555;">For assistance, please contact your authorized Morrisons representative or proceed through the official registration channel.</p>

              <p style="margin:0 0 8px 0; font-size:14px; line-height:22px; color:#555555;">Thank you for your continued trust and support.</p>

              <p style="margin:0; font-size:14px; line-height:22px; color:#004e37; font-weight:bold;">Morrisons Philippines</p>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 36px 26px 36px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7faf7; border-left:4px solid #d4af37;">
                <tr>
                  <td style="padding:14px 16px; font-size:12px; line-height:19px; color:#666666;">Slots are subject to availability and may close without prior notice once fully reserved. Please review all applicable terms before proceeding.</td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td align="center" style="background-color:#eef3ef; padding:22px 30px; border-top:1px solid #dfe6df;">
              <p style="margin:0 0 6px 0; font-size:12px; line-height:18px; color:#555555;">This is an official communication from Morrisons Philippines.</p>
              <p style="margin:0; font-size:12px; line-height:18px; color:#777777;">Growing Value, Together.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
