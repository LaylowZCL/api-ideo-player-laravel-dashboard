@extends('layouts.app-docs')

@section('title', 'Manual de Utilização da API')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual de Utilização da API</h1>

  <section class="doc-section">
    <h2>1. Pré-requisitos de instalação</h2>
    <ul>
      <li>Servidor Linux recomendado (Ubuntu LTS ou equivalente).</li>
      <li>PHP e extensões compatíveis com a versão Laravel em uso.</li>
      <li>MySQL/MariaDB com backups automáticos.</li>
      <li>Servidor web (Nginx/Apache) com HTTPS obrigatório.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>2. Instalação base</h2>
    <ol>
      <li>Configurar variáveis em <code>.env</code> (DB, APP_URL, chaves).</li>
      <li>Executar migrações: <code>php artisan migrate</code>.</li>
      <li>Garantir permissões de escrita em <code>storage</code> e <code>bootstrap/cache</code>.</li>
      <li>Activar supervisor para filas agendadas, se aplicável.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>3. Modelo de autenticação de clientes Electron</h2>
    <p>A aplicação desktop autentica-se por credenciais técnicas hardcoded (sem login de utilizador final).</p>
    <ul>
      <li>Credenciais transportadas por cabeçalhos técnicos (API key e client ID).</li>
      <li>Validação no middleware da API.</li>
      <li>Rotação de credenciais implica novo build e redeploy dos clientes.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>4. Endpoints críticos</h2>
    <ul>
      <li><code>GET /api/client/schedules</code> - horários activos por cliente.</li>
      <li><code>GET /api/client/videos/next</code> - próximo vídeo elegível.</li>
      <li><code>POST /api/videos/report</code> - eventos de reprodução.</li>
      <li><code>GET /api/client/ping</code> - heartbeat do cliente.</li>
    </ul>
    <p>Todos os endpoints devem ser protegidos por autenticação e limitação de taxa.</p>
  </section>

  <section class="doc-section">
    <h2>5. Publicação da documentação da solução</h2>
    <p>Todo o manual deve estar publicado no domínio da API:</p>
    <ul>
      <li><code>https://dominiodaapi.com/documentacao</code></li>
      <li><code>https://dominiodaapi.com/documentacao/*</code></li>
    </ul>
    <p>Inclui também o manual da app desktop.</p>
  </section>

  <section class="doc-section">
    <h2>6. Escalabilidade para 50 000+ utilizadores</h2>
    <ul>
      <li>Colocar a API atrás de balanceador com múltiplas instâncias.</li>
      <li>Aplicar cache em leituras de agenda e metadados.</li>
      <li>Separar tráfego interno e externo por rede/controlos.</li>
      <li>Usar observabilidade centralizada: logs, métricas e alertas.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>7. Segurança e continuidade</h2>
    <ul>
      <li>Rotação periódica de chaves de API.</li>
      <li>Backups diários da base de dados com testes de restauro.</li>
      <li>Plano de contingência para indisponibilidade de rede/API.</li>
      <li>Registo de auditoria para acções administrativas.</li>
    </ul>
  </section>
</main>
@endsection
