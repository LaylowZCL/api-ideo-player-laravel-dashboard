<!doctype html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Documentação BancoMoc')</title>
  <link rel="icon" type="image/png" href="{{ asset('docs-assets/images/logo-bm.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{ asset('docs-assets/css/styles.css') }}" rel="stylesheet">
</head>
<body>
  <header id="docs-header" class="docs-header">
    <nav class="navbar navbar-dark navbar-bm shadow-sm">
      <div class="container-fluid">
        <a class="navbar-brand brand-wrap fw-semibold" href="{{ route('documentacao.index') }}">
          <img src="{{ asset('docs-assets/images/logo-bm.png') }}" alt="Logo BancoMoc" class="brand-logo">
          <span>Documentação BancoMoc</span>
        </a>
        @hasSection('subtitle')
          <span class="navbar-text text-white-50">@yield('subtitle')</span>
        @endif
      </div>
    </nav>
    <div class="docs-submenu" aria-label="Submenu da documentação">
      <div class="container-fluid docs-submenu-links">
        <a href="{{ route('documentacao.manual-solucao-mista') }}">Manual da Solução Mista</a>
        <span class="docs-submenu-sep">|</span>
        <a href="{{ route('documentacao.manual-api') }}">Manual de Utilização da API</a>
        <span class="docs-submenu-sep">|</span>
        <a href="{{ route('documentacao.manual-dashboard-web') }}">Manual de Utilização do Dashboard Web</a>
        <span class="docs-submenu-sep">|</span>
        <a href="{{ route('documentacao.manual-app-electron') }}">Manual de Utilização da Aplicação Desktop</a>
        <span class="docs-submenu-sep">|</span>
        <a href="{{ route('documentacao.ficha-tecnica') }}">Ficha Técnica</a>
      </div>
    </div>
  </header>

  @yield('content')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('docs-assets/js/app.js') }}"></script>
</body>
</html>
