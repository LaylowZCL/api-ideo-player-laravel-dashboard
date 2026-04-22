@extends('layouts.app-docs')

@section('title', 'Documentação BancoMoc')
@section('subtitle', 'Guia Oficial de Utilização')

@section('content')
<main class="container py-4">
  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title">Manuais</h5>
          <div class="list-group list-group-flush mt-3">
            <a class="list-group-item list-group-item-action active" href="{{ route('docs.index') }}">Início</a>
            <a class="list-group-item list-group-item-action" href="{{ route('docs.page', ['slug' => 'manual-solucao-mista']) }}">Manual da Solução Mista</a>
            <a class="list-group-item list-group-item-action" href="{{ route('docs.page', ['slug' => 'manual-api']) }}">Manual de Utilização da API</a>
            <a class="list-group-item list-group-item-action" href="{{ route('docs.page', ['slug' => 'manual-dashboard-web']) }}">Manual de Utilização do Dashboard Web</a>
            <a class="list-group-item list-group-item-action" href="{{ route('docs.page', ['slug' => 'manual-aplicacao-desktop']) }}">Manual de Utilização da Aplicação Desktop</a>
            <a class="list-group-item list-group-item-action" href="{{ route('docs.page', ['slug' => 'ficha-tecnica']) }}">Ficha Técnica</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h1 class="h3 mb-3">Centro de Documentação da Aplicação</h1>
          <p class="lead mb-3">Este espaço reúne os manuais oficiais para utilização, administração e suporte da plataforma de gestão e distribuição de vídeos do Banco de Moçambique.</p>

          <div class="doc-kpis mb-3">
            <div class="doc-kpi"><strong>Módulos principais</strong> Dashboard, API e Aplicação Desktop</div>
            <div class="doc-kpi"><strong>Perfis de utilização</strong> Operadores, gestores e administradores</div>
            <div class="doc-kpi"><strong>Sistemas operativos</strong> Windows e macOS</div>
          </div>

          <p>Na documentação vais encontrar instruções para:</p>
          <ul>
            <li>aceder à aplicação e compreender o fluxo de autenticação;</li>
            <li>usar cada área do dashboard com confiança no dia a dia;</li>
            <li>gerir vídeos, agendamentos, utilizadores e entidades auxiliares;</li>
            <li>consultar relatórios, auditoria e configurações;</li>
            <li>entender como a solução funciona de ponta a ponta.</li>
          </ul>

          <div class="alert alert-bm mt-4 mb-3">
            <strong>Recomendação:</strong>
            comece pelo <em>Manual da Solução Mista</em> para ter uma visão geral e, em seguida, avance para o <em>Manual do Dashboard Web</em>, que explica a operação diária em detalhe.
          </div>

          <section class="doc-section mt-4">
            <h2>Por onde começar</h2>
            <div class="table-responsive">
              <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Se pretende...</th>
                    <th>Manual recomendado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Entender a arquitetura e o funcionamento global da solução</td>
                    <td><a href="{{ route('docs.page', ['slug' => 'manual-solucao-mista']) }}">Manual da Solução Mista</a></td>
                  </tr>
                  <tr>
                    <td>Aprender a operar a plataforma no dia a dia</td>
                    <td><a href="{{ route('docs.page', ['slug' => 'manual-dashboard-web']) }}">Manual de Utilização do Dashboard Web</a></td>
                  </tr>
                  <tr>
                    <td>Consultar detalhes técnicos da API e integração</td>
                    <td><a href="{{ route('docs.page', ['slug' => 'manual-api']) }}">Manual de Utilização da API</a></td>
                  </tr>
                  <tr>
                    <td>Compreender o cliente desktop e o processo de instalação</td>
                    <td><a href="{{ route('docs.page', ['slug' => 'manual-aplicacao-desktop']) }}">Manual de Utilização da Aplicação Desktop</a></td>
                  </tr>
                  <tr>
                    <td>Ver a ficha resumida da solução</td>
                    <td><a href="{{ route('docs.page', ['slug' => 'ficha-tecnica']) }}">Ficha Técnica</a></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section class="doc-section mt-4">
            <h2>Áreas cobertas nesta documentação</h2>
            <ul class="mb-0">
              <li><strong>Autenticação e acesso:</strong> login, primeiro acesso, recuperação de palavra-passe e 2FA.</li>
              <li><strong>Painel principal:</strong> leitura dos indicadores e atalhos mais importantes.</li>
              <li><strong>Gestão de vídeos:</strong> carregamento, pesquisa, edição, exclusão e sincronização.</li>
              <li><strong>Agendamentos:</strong> criação, atualização, duplicação, ativação e remoção.</li>
              <li><strong>Administração:</strong> utilizadores, grupos, campanhas, clientes, alvos AD e configurações.</li>
              <li><strong>Monitorização:</strong> relatórios, auditoria, resolução de problemas e boas práticas.</li>
            </ul>
          </section>

          <a class="btn btn-bm mt-3" href="{{ route('docs.page', ['slug' => 'manual-solucao-mista']) }}">Começar pelo manual principal</a>
        </div>
      </div>
    </div>
  </div>
</main>

<footer class="container pb-4">
  <hr>
  <div class="d-flex justify-content-between">
    <span>BancoMoc - Documentação Oficial da Aplicação</span>
    <span id="ano-actual"></span>
  </div>
</footer>
@endsection
