<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Construction ERP</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card glass-panel animate-fade-in">
            <h1 style="color: var(--accent-yellow); margin-bottom: 24px;">Construction ERP</h1>
            @yield('content')
        </div>
    </div>
</body>
</html>
