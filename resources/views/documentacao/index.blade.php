@extends('documentacao.layout')

@section('title', 'Documentação BancoMoc')
@section('subtitle', 'Solução Mista de Disponibilização de Vídeos')

@section('content')
<main class="container py-4">
  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Manuais</h5>
          <div class="list-group list-group-flush mt-3">
            <a class="list-group-item list-group-item-action active" href="{{ route('documentacao.index') }}">Início</a>
            <a class="list-group-item list-group-item-action" href="{{ route('documentacao.manual-solucao-mista') }}">Manual da Solução Mista</a>
            <a class="list-group-item list-group-item-action" href="{{ route('documentacao.manual-api') }}">Manual de Utilização da API</a>
            <a class="list-group-item list-group-item-action" href="{{ route('documentacao.manual-dashboard-web') }}">Manual de Utilização do Dashboard Web</a>
            <a class="list-group-item list-group-item-action" href="{{ route('documentacao.manual-app-electron') }}">Manual de Utilização da Aplicação Desktop</a>
            <a class="list-group-item list-group-item-action" href="{{ route('documentacao.ficha-tecnica') }}">Ficha Técnica</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h1 class="h3 mb-3">Centro de Documentação</h1>
          <p class="lead mb-3">Documentação de instalação, operação e suporte para ambiente de grande escala.</p>

          <div class="doc-kpis mb-3">
            <div class="doc-kpi"><strong>Escala-alvo</strong> 50 000+ utilizadores</div>
            {{-- <div class="doc-kpi"><strong>Sistemas operativos</strong> Windows, macOS, Linux</div> --}}
            <div class="doc-kpi"><strong>Sistemas operativos</strong> Windows, macOS</div>
            <div class="doc-kpi"><strong>Modos de execução</strong> Dashboard Web + Aplicação Desktop</div>
          </div>

          <p>Este portal inclui instruções para:</p>
          <ul>
            <li>Instalação por componente (API, Dashboard e aplicação desktop).</li>
            <li>Configuração para produção, segurança e monitorização.</li>
            <li>Operação diária, troubleshooting e boas práticas de continuidade.</li>
          </ul>

          <div class="alert alert-bm mt-4 mb-3">
            <strong>Manual oficial de manutenção da Aplicação Desktop:</strong>
            <code>docs/manual-utilizacao-app-electron.md</code> no repositório
            <a href="https://github.com/LaylowZCL/popup-video-player-with-Scheduler" target="_blank" rel="noopener noreferrer">popup-video-player-with-Scheduler</a>.
          </div>
          <a class="btn btn-bm" href="{{ route('documentacao.manual-solucao-mista') }}">Começar pelo manual principal</a>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="container pb-4">
  <hr>
  <div class="d-flex justify-content-between">
    <span>BancoMoc - Documentação Técnica</span>
    <span id="ano-actual"></span>
  </div>
</footer>
@endsection
