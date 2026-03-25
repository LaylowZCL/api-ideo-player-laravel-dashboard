<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VideoScheduler')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-bm.png') }}">
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/js/app.js'])

    @stack('styles')

    <style>
        .sair:hover {
            color: #fff !important;
        }

    </style>
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
                        <h6 class="mb-0 text-primary">Video Scheduler</h6>
                        <small class="text-muted">by: Ideias</small>
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
                    <a href="{{ route('reports') }}" class="nav-btn {{ request()->routeIs('reports') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Relatórios</span>
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
                    <a href="{{ route('users') }}" class="nav-btn {{ request()->routeIs('users') ? 'active' : '' }}">
                        <i class="bi bi-activity"></i>
                        <span>Usuários</span>
                    </a>
                    @can('isAdmin')
                    <div class="mt-3 text-uppercase text-muted small" style="letter-spacing:0.08em;">
                        Administração
                    </div>
                    <a href="{{ route('admin.groups') }}" class="nav-btn {{ request()->routeIs('admin.groups') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span>Grupos</span>
                    </a>
                    <a href="{{ route('admin.targets') }}" class="nav-btn {{ request()->routeIs('admin.targets') ? 'active' : '' }}">
                        <i class="bi bi-diagram-3"></i>
                        <span>Alvos AD</span>
                    </a>
                    <a href="{{ route('admin.clients') }}" class="nav-btn {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                        <i class="bi bi-pc-display"></i>
                        <span>Clientes</span>
                    </a>
                    <a href="{{ route('admin.campaigns') }}" class="nav-btn {{ request()->routeIs('admin.campaigns') ? 'active' : '' }}">
                        <i class="bi bi-flag"></i>
                        <span>Campanhas</span>
                    </a>
                    <a href="{{ route('admin.logs') }}" class="nav-btn {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
                        <i class="bi bi-clipboard-data"></i>
                        <span>Logs</span>
                    </a>
                    @endcan
                    {{--
                    <a href="{{ route('settings') }}" class="nav-btn {{ request()->routeIs('settings.index') ? 'active' : '' }}">
                        <i class="bi bi-gear"></i>
                        <span>Configurações</span>
                    </a>
                    --}}
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


            <div class="p-3 border-top border-secondary">
                <div class="d-flex align-items-center gap-3 p-3 bg-secondary rounded">
                    <div>
                        <div class="small fw-medium">
                            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                    @csrf
                </form>
                <button type="button" class="btn btn-link sair" style="
    color: #eab308 !important;
    font-size: 20px;
    font-weight: 600;
    text-transform: uppercase;
"
                    onclick="event.preventDefault(); 
                    document.getElementById('logout-form').submit();">
                        Sair
                </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="l" style="position: absolute; bottom: 20px; width: 100%;">
                
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
