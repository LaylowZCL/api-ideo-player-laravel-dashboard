{{--

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VideoScheduler')</title>
    <meta name="description" content="Dashboard de controle para aplicação de vídeos agendados">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Antonio:wght@100..700&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Exo+2:ital,wght@0,100..900;1,100..900&family=Exo:ital,wght@0,100..900;1,100..900&family=Fredoka:wght@300..700&family=Glory:ital,wght@0,100..800;1,100..800&family=Google+Sans+Flex:opsz,wght@6..144,1..1000&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Josefin+Sans:ital,wght@0,100..700;1,100..700&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Sunflower:wght@300&family=Unica+One&display=swap" rel="stylesheet">
    <script>
        window.APP_CONFIG = {
            apiEndpoint: @json(config('services.video_api.endpoint'))
        };
    </script>

    -- Bootstrap CSS 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     Bootstrap Icons 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
     Custom CSS 
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">

    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-dark text-light">
    <div class="d-flex min-vh-100">
       

        <main class="flex-fill p-4 overflow-auto">

            <div id="app">  
                @yield('content')
            </div>
        </main>
    </div>


    <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    @stack('scripts')
</body>
</html>
--}}




<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.84.0">
    <title>@yield('title', 'VideoScheduler')</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.0/examples/sign-in/">

            <!-- Bootstrap core CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

            <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-bm.png') }}">
        <meta name="theme-color" content="#7952b3">

        <style>
            html,
            body {
                height: 100%;
            }

            body {
                display: flex;
                align-items: center;
                padding-top: 40px;
                padding-bottom: 40px;
                background-color: #023d7c;
                font-family: "Segoe UI", "Inter", system-ui, -apple-system, sans-serif;
            }

            .form-signin {
                width: 100%;
                max-width: 330px;
                padding: 15px;
                margin: auto;
            }

            .form-signin .checkbox {
                font-weight: 400;
            }

            .form-signin .form-floating:focus-within {
                z-index: 2;
            }

            .form-signin input[type="email"] {
                margin-bottom: -1px;
                border-bottom-right-radius: 0;
                border-bottom-left-radius: 0;
            }

            .form-signin input[type="password"] {
                margin-bottom: 10px;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
            }

            .bd-placeholder-img {
                font-size: 1.125rem;
                text-anchor: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                user-select: none;
            }

            @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                font-size: 3.5rem;
                }
            }

            .sair:hover {
                color: #fff !important;
            }

            .form-signin {
                border: solid 1px #bd8b41ff !important;
                border-radius:20px !important;
                padding: 30px;
                filter: drop-shadow(0px 0px 3px gray);
                background: #0052a9;
            }
            .btn-primary{
                background: #ba9a69;
                border-color: #8b724cff;
            }
            .btn-primary:hover{
                background: #885b18ff;
                border-color: #8b724cff;
            }
            h1, label ,a ,p {
                color: #ba9a69 !important;
            }

            .otp-card {
                text-align: left;
                word-break: break-word;
                overflow-wrap: anywhere;
                font-size: 0.9rem;
            }

            .otp-value {
                font-family: "Segoe UI", "Inter", system-ui, -apple-system, sans-serif;
                font-weight: 600;
                word-break: break-all;
                overflow-wrap: anywhere;
            }

            .otp-raw {
                font-size: 0.78rem;
                line-height: 1.3;
                word-break: break-all;
                overflow-wrap: anywhere;
            }

            .qr-image {
                max-width: 200px;
                width: 60vw;
                height: auto;
                border-radius: 12px;
                background: #fff;
                padding: 8px;
            }

            @media (max-width: 420px) {
                .form-signin {
                    max-width: 92vw;
                    padding: 20px;
                }

                .otp-raw {
                    display: none;
                }
            }
        </style>

        <!-- Custom styles for this template -->
        <link href="signin.css" rel="stylesheet">
            @vite(['resources/js/app.js'])

            @stack('styles')
        </head>
    <body class="text-center">

        @yield('content')
        
        

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>
