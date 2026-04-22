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
  <nav class="navbar navbar-dark navbar-bm shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand brand-wrap fw-semibold" href="{{ route('docs.index') }}">
        <img src="{{ asset('docs-assets/images/logo-bm.png') }}" alt="Logo BancoMoc" class="brand-logo">
        <span>Documentação BancoMoc</span>
      </a>
      @hasSection('subtitle')
        <span class="navbar-text text-white-50">@yield('subtitle')</span>
      @endif
    </div>
  </nav>

  @yield('content')

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('docs-assets/js/app.js') }}"></script>
</body>
</html>
