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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-dark text-light">
    <div class="d-flex min-vh-100">
        <!-- Sidebar -->
        <div class="sidebar bg-dark border-end border-secondary" id="sidebar">
            <div class="p-3 border-bottom border-secondary">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-2 p-2" style="background-color:#ffffff !important; width:70px;">
                        <img src="{{ asset('assets/images/logo-bm.png') }}" style="width:100%">
                    </div>
                    <div>
                        <h6 class="mb-0 text-white">Video Scheduler</h6>
                        <small class="text-muted">BY ZK</small>
                    </div>
                </div>
            </div>

            <nav class="flex-fill p-3">
                <div class="nav-menu">
                    <a href="{{ route('dashboard') }}" class="nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-display"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('schedule') }}" class="nav-btn {{ request()->routeIs('schedule.index') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Agendamentos</span>
                    </a>
                    <a href="{{ route('videos') }}" class="nav-btn {{ request()->routeIs('videos') ? 'active' : '' }}">
                        <i class="bi bi-camera-video"></i>
                        <span>Vídeos</span>
                    </a>
                    {{--
                    <a href="{{ route('preview') }}" class="nav-btn {{ request()->routeIs('preview') ? 'active' : '' }}">
                        <i class="bi bi-play-circle"></i>
                        <span>Preview</span>
                    </a>
                    <a href="{{ route('logs') }}" class="nav-btn {{ request()->routeIs('logs') ? 'active' : '' }}">
                        <i class="bi bi-activity"></i>
                        <span>Logs</span>
                    </a>
                    --}}
                    <a href="{{ route('settings') }}" class="nav-btn {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </a>
                </div>
            </nav>

            <div class="p-3 border-top border-secondary">
                <div class="d-flex align-items-center gap-3 p-3 bg-secondary rounded">
                    <div class="bg-success rounded-circle" style="width: 8px; height: 8px;"></div>
                    <div>
                        <div class="small fw-medium">Sistema Ativo</div>
                        <div class="text-muted" style="font-size: 0.75rem;">Online • 2h 34m</div>
                    </div>
                </div>
            </div>
        </div>

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
    <!-- Custom JS -->
{{--    <script src="{{ asset('assets/js/app.js') }}"></script>  --}}

    @stack('scripts')
</body>
</html>
