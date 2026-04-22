@extends('layouts.app-docs')

@section('title', 'Ficha Técnica')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Ficha Técnica</h1>
  <p class="text-secondary">Resumo técnico e funcional da solução BM Video Schedule Manager.</p>

  <section class="doc-section">
    <h2>1. Identificação da solução</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">Nome da solução</th><td>BM Video Schedule Manager</td></tr>
          <tr><th>Tipo de solução</th><td>Dashboard web + API Laravel para agendamento e distribuição de vídeos</td></tr>
          <tr><th>Versão funcional</th><td>1.0 (baseada no estado atual do repositório)</td></tr>
          <tr><th>Estado</th><td>ACTIVO</td></tr>
          <tr><th>Data de entrada em produção</th><td>A confirmar pela operação</td></tr>
          <tr><th>Âmbito geográfico</th><td>Moçambique</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>2. Intervenientes</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Função</th>
            <th>Nome</th>
            <th>Organização</th>
            <th>Contacto</th>
            <th>Responsabilidades</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Gestão do projecto</td>
            <td>Pereira Fernando</td>
            <td>Idéias</td>
            <td>admin@ideiascomunicacao.com</td>
            <td>Coordenação, priorização e acompanhamento funcional.</td>
          </tr>
          <tr>
            <td>Engenheiro de Software</td>
            <td>Fernando Zucula</td>
            <td>Idéias</td>
            <td>me@fernandozucula.com</td>
            <td>Desenvolvimento, integração técnica, manutenção evolutiva e correcções.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>3. Componentes e repositórios</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr><th>Componente</th><th>Repositório</th><th>Branch principal</th><th>Versão</th><th>Responsável</th></tr>
        </thead>
        <tbody>
          <tr><td>Dashboard/API Laravel</td><td>api-video-player-laravel-dashboard</td><td>main (a confirmar)</td><td>Laravel 10.x / PHP 8.1+</td><td>Equipa de engenharia</td></tr>
          <tr><td>Frontend Web</td><td>Mesmo repositório (Vite + Vue 3)</td><td>main (a confirmar)</td><td>Vue 3.5 / Vite 5</td><td>Equipa de engenharia</td></tr>
          <tr><td>Aplicação Desktop</td><td>Repositório Electron dedicado (externo a este projecto)</td><td>A confirmar</td><td>A confirmar</td><td>Equipa de cliente desktop</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>4. Infra-estrutura e ambientes</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">Ambiente de desenvolvimento</th><td>Local via <code>php artisan serve</code> + <code>npm run dev</code></td></tr>
          <tr><th>Ambiente de homologação</th><td>A confirmar</td></tr>
          <tr><th>Ambiente de produção</th><td>A confirmar</td></tr>
          <tr><th>Backend</th><td>Laravel 10.x, PHP 8.1+, Sanctum, Laravel UI</td></tr>
          <tr><th>Frontend</th><td>Vue 3.5, Vite 5, Bootstrap 5, Chart.js 4</td></tr>
          <tr><th>Base de dados</th><td>MySQL (pré-requisito oficial do projecto)</td></tr>
          <tr><th>Armazenamento de vídeos</th><td>Serviço local via URL pública (ex.: <code>/videos/*</code>), cache configurável no cliente desktop</td></tr>
          <tr><th>Sistema de logs</th><td>Laravel logging (<code>storage/logs</code>) + tabela <code>logs</code></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>5. Segurança e conformidade</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">TLS/HTTPS obrigatório</th><td>Sim (obrigatório em produção)</td></tr>
          <tr><th>Autenticação web</th><td>Laravel Auth (sessão e credenciais de utilizador)</td></tr>
          <tr><th>Autenticação técnica API (cliente desktop)</th><td><code>X-API-Key</code> + <code>X-Client-ID</code> validados em middleware dedicado</td></tr>
          <tr><th>Gestão de perfis e permissões</th><td>Perfis e permissões definidos por utilizador conforme regras da aplicação</td></tr>
          <tr><th>Auditoria de acções</th><td>Registos em logs de aplicação e eventos de reprodução (<code>video_reports</code>)</td></tr>
          <tr><th>Política de rotação de chaves</th><td>A definir (recomendado: trimestral ou em incidente)</td></tr>
          <tr><th>Norma/compliance aplicável</th><td>A confirmar conforme política interna do cliente</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>6. Operação e suporte</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">Módulos operacionais</th><td>Dashboard, Vídeos, Agendamentos, Utilizadores, Logs, Preview, Configurações</td></tr>
          <tr><th>Documentação oficial</th><td><code>/docs</code> e respetivas páginas internas</td></tr>
          <tr><th>Integração técnica</th><td>Realizada pela API e pelos serviços internos da solução, conforme configuração operacional</td></tr>
          <tr><th>SLA (disponibilidade)</th><td>A definir com operação/negócio</td></tr>
          <tr><th>Janela de manutenção</th><td>A definir</td></tr>
          <tr><th>Canal de suporte N1</th><td>A definir</td></tr>
          <tr><th>Canal de suporte N2/N3</th><td>Equipa de engenharia</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>7. Backup e recuperação</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">Periodicidade de backup</th><td>A definir (recomendado: diário)</td></tr>
          <tr><th>Retenção de backups</th><td>A definir (recomendado: 30-90 dias)</td></tr>
          <tr><th>Objectos críticos</th><td>Base de dados, ficheiros de vídeo, configuração de ambiente (<code>.env</code>)</td></tr>
          <tr><th>RPO</th><td>A definir</td></tr>
          <tr><th>RTO</th><td>A definir</td></tr>
          <tr><th>Teste de restauro</th><td>A definir (recomendado: trimestral)</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <section class="doc-section">
    <h2>8. Controlo documental</h2>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <tbody>
          <tr><th style="width:35%">Autor do documento</th><td>Equipa técnica BM Video Schedule Manager</td></tr>
          <tr><th>Revisor</th><td>A confirmar</td></tr>
          <tr><th>Aprovador</th><td>A confirmar</td></tr>
          <tr><th>Versão do documento</th><td>1.0</td></tr>
          <tr><th>Data da última revisão</th><td>{{ date('Y-m-d') }}</td></tr>
          <tr><th>Próxima revisão prevista</th><td>{{ date('Y-m-d', strtotime('+6 months')) }}</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</main>
@endsection
