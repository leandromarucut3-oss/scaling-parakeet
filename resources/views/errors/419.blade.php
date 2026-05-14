<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Expired</title>
    <style>
        body { margin: 0; padding: 0; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f8fafc; color: #0f172a; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { max-width: 640px; padding: 2rem; background: #ffffff; border-radius: 1rem; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 1rem; font-size: 2rem; }
        p { margin: 0 0 1.25rem; line-height: 1.75; color: #475569; }
        a { display: inline-flex; align-items: center; justify-content: center; padding: 0.85rem 1.4rem; border-radius: 0.75rem; background: #047857; color: white; text-decoration: none; font-weight: 600; }
        a:hover { background: #065f46; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Session Expired</h1>
        <p>Your session has expired or the form token is no longer valid. Please refresh the page and try again. If you were submitting data, it has not been processed.</p>
        <a href="{{ url()->previous() ?? route('dashboard') }}">Go back</a>
    </div>
</body>
</html>
