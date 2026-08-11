<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sneakers Colima - Iniciar Sesion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 40px;
        }
        .login-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 32px;
            justify-content: center;
        }
        .login-brand-icon {
            width: 48px;
            height: 48px;
            background: var(--accent-gold);
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            color: #000;
        }
        .login-brand h1 {
            font-size: 22px;
            font-weight: 600;
        }
        .login-brand span {
            font-size: 12px;
            color: var(--text-muted);
        }
        .login-title {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-title h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .login-title p {
            font-size: 13px;
            color: var(--text-muted);
        }
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .form-group input {
            padding: 12px 14px;
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 14px;
            color: var(--text-primary);
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            border-color: var(--accent-gold);
            outline: none;
        }
        .form-error {
            font-size: 12px;
            color: var(--red);
            margin-top: 4px;
        }
        .login-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .login-remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-gold);
        }
        .login-submit {
            width: 100%;
            padding: 14px;
            background: var(--accent-gold);
            color: #000;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-submit:hover {
            background: var(--accent-gold-hover);
        }
        .login-security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 20px;
        }
        .login-security-dot {
            width: 6px;
            height: 6px;
            background: var(--green);
            border-radius: 50%;
        }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
</body>
</html>
