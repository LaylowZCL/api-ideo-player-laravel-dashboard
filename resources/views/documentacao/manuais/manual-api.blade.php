@extends('documentacao.layout')

@section('title', 'Manual de Utilização da API')

@section('content')
<main class="container py-4">
  <a href="{{ route('documentacao.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual de Utilização da API</h1>

  <section class="doc-section">
    <h2>1. Ambiente-alvo de alojamento</h2>
    <ul>
      <li><strong>Sistema operativo:</strong> Rocky Linux 9.x.</li>
      <li><strong>Base de dados:</strong> PostgreSQL 14+ (recomendado 15/16).</li>
      <li><strong>Servidor web:</strong> Nginx (recomendado) ou Apache.</li>
      <li><strong>HTTPS:</strong> obrigatório em produção.</li>
      <li><strong>Document root:</strong> apontar para <code>/public</code>.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>2. Requisitos mínimos de infraestrutura</h2>
    <h3>2.1 Produção inicial</h3>
    <ul>
      <li>CPU: 4 vCPU</li>
      <li>RAM: 8 GB</li>
      <li>Disco: 120 GB SSD (mínimo)</li>
    </ul>
    <h3>2.2 Produção estável (recomendado)</h3>
    <ul>
      <li>CPU: 8 vCPU</li>
      <li>RAM: 16 GB</li>
      <li>Disco: 250+ GB SSD/NVMe</li>
      <li>Volume separado para vídeos e backups</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>3. Stack técnica obrigatória</h2>
    <ul>
      <li>PHP 8.1+ (ideal 8.2) com PHP-FPM.</li>
      <li>Composer 2.x.</li>
      <li>Node.js 20 LTS + npm 10+ (para build frontend).</li>
      <li>Extensões PHP: <code>mbstring</code>, <code>xml</code>, <code>curl</code>, <code>zip</code>, <code>gd</code>, <code>bcmath</code>, <code>fileinfo</code>, <code>opcache</code>, <code>pdo</code>, <code>pgsql</code>.</li>
      <li><code>ffmpeg</code> e <code>ffprobe</code> instalados no sistema.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>4. Rede e portas</h2>
    <ul>
      <li>Abrir externamente: <code>443/TCP</code> (HTTPS).</li>
      <li><code>80/TCP</code> opcional apenas para redirecionamento para 443.</li>
      <li><code>22/TCP</code> apenas administração, com whitelist de IP.</li>
      <li>Não expor: <code>5432</code> (PostgreSQL), <code>6379</code> (Redis), portas de desenvolvimento (ex.: <code>8081</code>).</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>5. Segurança e configuração obrigatória</h2>
    <p><strong>.env mínimo para produção:</strong></p>
    <ul>
      <li><code>APP_ENV=production</code> e <code>APP_DEBUG=false</code>.</li>
      <li><code>APP_URL=https://SEU_DOMINIO</code>.</li>
      <li><code>DB_CONNECTION=pgsql</code>, <code>DB_HOST</code>, <code>DB_PORT=5432</code>, <code>DB_DATABASE</code>, <code>DB_USERNAME</code>, <code>DB_PASSWORD</code>.</li>
      <li><code>APP_API_KEY</code> e <code>APP_CLIENT_ID</code> definidos.</li>
      <li><code>VIDEO_API_BASE_URL</code> e <code>VIDEO_API_ENDPOINT</code> alinhados com o domínio final.</li>
    </ul>
    <p>Reforços de segurança: TLS válido, rate limit, cabeçalhos de segurança e princípio de privilégio mínimo no PostgreSQL.</p>
  </section>

  <section class="doc-section">
    <h2>6. Processo de deploy (produção)</h2>
    <ol>
      <li><code>composer install --no-dev --optimize-autoloader</code></li>
      <li><code>npm install</code> e <code>npm run build</code></li>
      <li>Configurar <code>.env</code> de produção</li>
      <li><code>php artisan key:generate</code></li>
      <li><code>php artisan migrate --force</code></li>
      <li><code>php artisan storage:link</code></li>
      <li><code>php artisan optimize</code></li>
    </ol>
    <p>Garantir escrita em <code>storage/</code> e <code>bootstrap/cache/</code>.</p>
  </section>

  <section class="doc-section">
    <h2>7. Operação contínua</h2>
    <ul>
      <li>Processos essenciais ativos: Nginx/Apache, PHP-FPM, PostgreSQL.</li>
      <li>Agendador Laravel no cron: <code>* * * * * php /caminho/da/app/artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</code>.</li>
      <li>Se usar filas assíncronas, executar workers via Supervisor/systemd com restart automático.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>8. Backups e monitorização</h2>
    <ul>
      <li>Backups diários de PostgreSQL com retenção de 30 a 90 dias.</li>
      <li>Teste periódico de restauro (mínimo trimestral).</li>
      <li>Monitorizar disponibilidade HTTP, CPU/RAM/disco, logs Laravel e uso do armazenamento de vídeos.</li>
      <li>Alertas recomendados: CPU&gt;85%, RAM&gt;85%, disco&gt;80%, aumento de 5xx.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>9. Publicação da documentação da solução</h2>
    <p>Todo o manual deve estar publicado no domínio da API:</p>
    <ul>
      <li><code>https://dominiodaapi.com/documentacao</code></li>
      <li><code>https://dominiodaapi.com/documentacao/*</code></li>
    </ul>
    <p>Inclui também o manual da app desktop.</p>
  </section>
</main>
@endsection
