<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: Arial, sans-serif; color:#111827; }
    .content { max-width:600px; margin:32px auto; padding:16px; }
    .btn { display:inline-block; background:#111827; color:#fff; padding:10px 16px; border-radius:6px; text-decoration:none; }
  </style>
</head>
<body>
  <div class="content">
    <h2>Congratulations {{ $user->name ?? ($user['name'] ?? 'Participant') }}!</h2>
    <p>Thank you for availing our <strong>{{ $package->name ?? ($package['name'] ?? '') }}</strong> package. Attached is your Certificate of Membership.</p>
    <p>If you need a different format, reply to this email and we'll assist.</p>
    <p>Best regards,<br/>The Team</p>
  </div>
</body>
</html>
