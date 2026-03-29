<!doctype html>
<html lang="pt-PT">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Erro') - Banco de Moçambique</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-bm.png') }}">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #0d5cb6 0%, #023d7c 55%, #01284f 100%);
            font-family: "Segoe UI", "Inter", system-ui, sans-serif;
            color: #ffffff;
            padding: 24px;
        }
        .error-card {
            width: min(720px, 100%);
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 28px;
            padding: 36px;
            box-shadow: 0 22px 60px rgba(3, 18, 38, 0.35);
            text-align: center;
            backdrop-filter: blur(10px);
        }
        .logo-wrap {
            width: 112px;
            height: 112px;
            margin: 0 auto 20px;
            background: #fff;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #c3a56b;
        }
        .logo-wrap img { width: 82px; height: 82px; object-fit: contain; }
        .error-code {
            font-size: 4rem;
            line-height: 1;
            font-weight: 700;
            color: #f6d9a6;
            margin-bottom: 12px;
        }
        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 12px;
        }
        .error-message {
            color: #dfeaff;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 24px;
        }
        .btn-home {
            display: inline-block;
            padding: 14px 24px;
            background: #c3a56b;
            color: #fff;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
        }
        .error-detail {
            margin-top: 18px;
            color: #c8d9f5;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="logo-wrap">
            <img src="{{ asset('assets/images/logo-bm.png') }}" alt="Banco de Moçambique">
        </div>
        <div class="error-code">@yield('code')</div>
        <div class="error-title">@yield('heading')</div>
        <div class="error-message">@yield('message')</div>
        <a class="btn-home" href="{{ auth()->check() ? route('dashboard') : route('login') }}">Voltar à aplicação</a>
        @if(!empty($exception?->getMessage()))
            <div class="error-detail">{{ $exception->getMessage() }}</div>
        @endif
    </div>
</body>
</html>
