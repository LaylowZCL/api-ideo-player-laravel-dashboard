@extends('documentacao.layout')

@section('title', 'Manual da Solução Mista')

@section('content')
<main class="container py-4">
  <a href="{{ route('documentacao.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual Descritivo da Solução Mista de Disponibilização de Vídeos</h1>
  <p class="text-secondary">Versão 1.2</p>

  <section class="doc-section">
    <h2>1. Objectivo e âmbito</h2>
    <p>Esta solução integra Dashboard/API (Laravel) e Aplicação Desktop para distribuição e reprodução de vídeos em múltiplos postos de trabalho.</p>
    {{-- <p>Foi concebida para operar em escala elevada, suportando mais de 50 000 utilizadores em ambientes Windows, macOS e Linux.</p> --}}
    <p>Foi concebida para operar em escala elevada, suportando mais de 50 000 utilizadores em ambientes Windows e macOS.</p>
  </section>

  <section class="doc-section">
    <h2>2. Arquitectura de referência</h2>
    <ul>
      <li><strong>Camada de gestão:</strong> Dashboard Web para operação e administração.</li>
      <li><strong>Camada de serviços:</strong> API para autenticação, agenda e telemetria.</li>
      <li><strong>Camada de execução:</strong> Aplicação Desktop em clientes finais.</li>
      <li><strong>Armazenamento:</strong> base de dados relacional e directoria de vídeos.</li>
    </ul>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/dashboard.png') }}" alt="Vista geral do dashboard">
      <figcaption>Painel principal de operação e monitorização.</figcaption>
    </figure>
  </section>

  <section class="doc-section">
    <h2>3. Decisão operacional de autenticação</h2>
    <p>A aplicação desktop utiliza autenticação hardcoded para reduzir assistência técnica de login em centenas de instalações.</p>
    <ul>
      <li>Sem autenticação interactiva por utilizador final.</li>
      <li>Credenciais técnicas embebidas no binário.</li>
      <li>Rotação de credenciais requer novo build e redistribuição.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>4. Instalação resumida</h2>
    <h3>4.1 API + Dashboard (servidor)</h3>
    <ol>
      <li>Provisionar servidor Rocky Linux 9.x com Nginx/Apache, PHP-FPM 8.1+ e Composer 2.x.</li>
      <li>Instalar PostgreSQL 14+ e criar base/utilizador dedicados da aplicação.</li>
      <li>Instalar Node.js 20 LTS para build frontend e <code>ffmpeg/ffprobe</code> para metadados de vídeo.</li>
      <li>Configurar <code>.env</code> com base de dados, URL da aplicação e credenciais de API.</li>
      <li>Executar migrações: <code>php artisan migrate</code>.</li>
      <li>Garantir escrita em <code>storage/</code> e <code>bootstrap/cache</code>.</li>
    </ol>
    <h3>4.2 Aplicação Desktop (postos)</h3>
    <ol>
      <li>Confirmar domínio final da API (ex.: <code>https://dominiodaapi.com</code>).</li>
      <li>Actualizar <code>BASE_URL</code> hardcoded para <code>https://dominiodaapi.com/api</code>.</li>
      {{-- <li>Compilar por sistema operativo (macOS, Windows, Linux).</li> --}}
      <li>Compilar por sistema operativo (macOS, Windows).</li>
      <li>Instalar aplicação com pacote gerado para cada plataforma.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>5. Operação diária</h2>
    <ol>
      <li>Publicar/actualizar vídeos no Dashboard.</li>
      <li>Criar ou rever agendamentos activos.</li>
      <li>Executar sincronização e confirmar estado da API.</li>
      <li>Monitorizar relatórios de reprodução e erros.</li>
    </ol>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/gerenciamento-de-videos.png') }}" alt="Gestão de vídeos">
      <figcaption>Gestão e sincronização de vídeos no Dashboard.</figcaption>
    </figure>
  </section>

  <section class="doc-section">
    <h2>6. Publicação da documentação</h2>
    <p>A documentação integral deve estar servida pela API em:</p>
    <ul>
      <li><code>https://dominiodaapi.com/documentacao</code></li>
      <li><code>https://dominiodaapi.com/documentacao/*</code></li>
    </ul>
    <p>Isto inclui o manual da app desktop (Electron).</p>
  </section>

  <section class="doc-section">
    <h2>7. Boas práticas para escala (50 000+ utilizadores)</h2>
    <ul>
      <li>Distribuir a API por múltiplas instâncias atrás de balanceador.</li>
      <li>Separar base de dados de escrita e leitura quando aplicável.</li>
      <li>Utilizar cache para respostas frequentes de agenda e metadados.</li>
      <li>Aplicar monitorização central de latência, erros e saúde de clientes.</li>
      <li>Definir janelas de manutenção e política de rollback.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>8. Segurança e conformidade</h2>
    <ul>
      <li>Forçar HTTPS/TLS entre clientes e API.</li>
      <li>Rodar chaves de API periodicamente.</li>
      <li>Restringir permissões por perfil (Administrador, Manager, Operador).</li>
      <li>Guardar logs de auditoria de criação, edição e eliminação.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>9. Manuais específicos</h2>
    <ul>
      <li><a href="{{ route('documentacao.manual-api') }}">Manual de Utilização da API</a></li>
      <li><a href="{{ route('documentacao.manual-dashboard-web') }}">Manual de Utilização do Dashboard Web</a></li>
      <li><a href="{{ route('documentacao.manual-app-electron') }}">Manual de Utilização da Aplicação Desktop</a></li>
      <li><a href="{{ route('documentacao.ficha-tecnica') }}">Ficha Técnica</a></li>
    </ul>
  </section>
</main>
@endsection
