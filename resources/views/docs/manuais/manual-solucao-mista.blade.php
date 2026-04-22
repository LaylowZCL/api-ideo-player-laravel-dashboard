@extends('layouts.app-docs')

@section('title', 'Manual da Solução Mista')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual da Solução Mista de Disponibilização de Vídeos</h1>
  <p class="text-secondary">Visão funcional e operacional da solução composta por Dashboard Web, API e Aplicação Desktop.</p>

  <section class="doc-section">
    <h2>1. O que é esta solução</h2>
    <p>A solução foi concebida para permitir a gestão centralizada de conteúdos de vídeo e respetivos agendamentos, com distribuição controlada para clientes finais. Em termos práticos, a equipa gestora administra vídeos e regras de execução no dashboard, enquanto os clientes desktop consomem essas instruções e reproduzem os conteúdos conforme definido.</p>
  </section>

  <section class="doc-section">
    <h2>2. Componentes principais</h2>
    <ul>
      <li><strong>Dashboard Web:</strong> interface de gestão para utilizadores operacionais e administrativos.</li>
      <li><strong>API:</strong> camada responsável por autenticação técnica, leitura de agendamentos, telemetria e integração.</li>
      <li><strong>Aplicação Desktop:</strong> cliente que executa os conteúdos nos postos finais.</li>
      <li><strong>Base de dados e armazenamento:</strong> sustentam a persistência das regras, registos e conteúdos associados.</li>
    </ul>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/painel-de-controle.png') }}" alt="Visão geral do dashboard">
      <figcaption>O dashboard web funciona como ponto central de operação da solução.</figcaption>
    </figure>
  </section>

  <section class="doc-section">
    <h2>3. Como os componentes interagem</h2>
    <ol>
      <li>Os utilizadores autenticados entram no dashboard e configuram vídeos, utilizadores, agendamentos e outros parâmetros.</li>
      <li>A informação fica registada na base de dados e disponibilizada pela API.</li>
      <li>A aplicação desktop consulta a API para obter instruções operacionais e reporta eventos de execução.</li>
      <li>O dashboard mostra relatórios, indicadores e logs para acompanhamento contínuo da operação.</li>
    </ol>
    <p>Este modelo permite separar claramente a gestão central da execução local.</p>
  </section>

  <section class="doc-section">
    <h2>4. Perfis de utilização</h2>
    <ul>
      <li><strong>Operador:</strong> acompanha e executa tarefas operacionais do dia a dia, conforme permissões atribuídas.</li>
      <li><strong>Gestor:</strong> supervisiona conteúdos, agendamentos e algumas áreas administrativas.</li>
      <li><strong>Administrador:</strong> possui acesso ampliado à gestão de utilizadores, configurações e áreas sensíveis.</li>
    </ul>
    <p>O que cada utilizador vê no sistema depende diretamente do seu perfil e das permissões atribuídas.</p>
  </section>

  <section class="doc-section">
    <h2>5. Fluxo operacional típico</h2>
    <ol>
      <li>Entrar no dashboard com credenciais válidas.</li>
      <li>Carregar ou atualizar vídeos disponíveis.</li>
      <li>Criar ou rever agendamentos ativos.</li>
      <li>Validar entidades complementares, como clientes, grupos, campanhas e alvos AD, quando aplicável.</li>
      <li>Acompanhar relatórios e logs para garantir que a execução ocorreu como previsto.</li>
    </ol>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/carregamento-de-video.png') }}" alt="Gestão operacional de conteúdos">
      <figcaption>A gestão de conteúdos e agendamentos acontece de forma centralizada no dashboard.</figcaption>
    </figure>
  </section>

  <section class="doc-section">
    <h2>6. Aplicação Desktop</h2>
    <p>A aplicação desktop é a componente responsável por executar os vídeos nos postos finais. Ao contrário do dashboard, ela não é operada com login tradicional por utilizador final. A comunicação com a API segue um modelo técnico controlado, definido pela organização.</p>
    <ul>
      <li>Recebe instruções e horários a partir da API.</li>
      <li>Executa os conteúdos conforme o agendamento configurado.</li>
      <li>Envia eventos de operação para acompanhamento e auditoria.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>7. Segurança e governação</h2>
    <ul>
      <li>O acesso ao dashboard depende de autenticação e permissões.</li>
      <li>A API deve operar com HTTPS e controlos de autenticação técnica adequados.</li>
      <li>As ações relevantes devem poder ser acompanhadas por logs e relatórios.</li>
      <li>Perfis e permissões devem ser atribuídos segundo o princípio do menor privilégio necessário.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>8. Onde consultar cada tema</h2>
    <ul>
      <li><a href="{{ route('docs.page', ['slug' => 'manual-dashboard-web']) }}">Manual de Utilização do Dashboard Web</a> para a operação diária da plataforma.</li>
      <li><a href="{{ route('docs.page', ['slug' => 'manual-api']) }}">Manual de Utilização da API</a> para contexto técnico e integração.</li>
      <li><a href="{{ route('docs.page', ['slug' => 'manual-aplicacao-desktop']) }}">Manual de Utilização da Aplicação Desktop</a> para instalação e uso do cliente final.</li>
      <li><a href="{{ route('docs.page', ['slug' => 'ficha-tecnica']) }}">Ficha Técnica</a> para a visão resumida da solução.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>9. URL oficial da documentação</h2>
    <p>A documentação oficial desta aplicação deve ser disponibilizada no caminho:</p>
    <ul class="mb-0">
      <li><code>/docs</code></li>
      <li><code>/docs/&lt;manual&gt;</code></li>
    </ul>
  </section>
</main>
@endsection
