<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin:0; padding:0; }
    .container { width: 100%; height:100vh; display:flex; align-items:center; justify-content:center; background: #f3f6f9; }
    .card { width: 1100px; height:700px; background: linear-gradient(180deg,#ffffff 0%,#f8fafc 100%); border-radius:18px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); padding:48px; box-sizing:border-box; position:relative; }
    .logo { position:absolute; top:36px; left:48px; font-weight:700; color:#0f172a; }
    .title { text-align:center; margin-top:40px; font-size:28px; color:#0f172a; letter-spacing:1px; }
    .name { text-align:center; font-size:48px; margin-top:30px; font-weight:700; color:#111827; }
    .meta { text-align:center; margin-top:18px; color:#6b7280; }
    .badge { position:absolute; right:48px; top:48px; background:#111827; color:#fff; padding:10px 16px; border-radius:8px; font-weight:600; }
    .footer { position:absolute; bottom:36px; left:48px; right:48px; display:flex; justify-content:space-between; color:#6b7280; font-size:14px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="logo">Your Organization</div>
      <div class="badge">Certificate</div>

      <div class="title">Certificate of Membership</div>

      <div class="name">{{ $user->name ?? ($user['name'] ?? 'Participant') }}</div>

      <div class="meta">has availed the <strong>{{ $package->name ?? ($package['name'] ?? 'Package') }}</strong> package</div>

      <div class="meta">Issued on {{ $issued_at }} | ID: {{ $certificate_id }}</div>

      <div class="footer">
        <div>Authorized by: <strong>Organization Name</strong></div>
        <div>Package: <strong>{{ $package->name ?? ($package['name'] ?? '') }}</strong></div>
      </div>
    </div>
  </div>
</body>
</html>
