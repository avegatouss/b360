<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'B360') }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-logo-text {
            font-size: 1.75rem;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: -0.04em;
        }

        .auth-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .auth-field {
            margin-bottom: 1rem;
        }

        .auth-field label {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.375rem;
        }

        .auth-field input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.9375rem;
            color: #111827;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .auth-field input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }

        .auth-link-right {
            font-size: 0.8125rem;
            color: #2563eb;
            text-decoration: none;
            font-weight: 400;
        }

        .auth-link-right:hover { text-decoration: underline; }

        .auth-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            color: #4b5563;
        }

        .auth-btn {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }

        .auth-btn:hover { background: #1d4ed8; }
        .auth-btn:active { background: #1e40af; }

        .auth-alert {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        .auth-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .auth-alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .auth-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .auth-footer a { color: #2563eb; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    {{ $slot }}
</body>
</html>
