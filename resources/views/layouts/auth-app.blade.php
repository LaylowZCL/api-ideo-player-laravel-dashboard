<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VideoScheduler')</title>
    <meta name="description" content="Dashboard de controle para aplicação de vídeos agendados">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">

    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-dark text-light">
    <div class="d-flex min-vh-100">
       

        <!-- Main Content -->
        <main class="flex-fill p-4 overflow-auto">

            <div id="app">  <!-- Must match mount selector -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Vue.js -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

    @stack('scripts')
</body>
</html>
