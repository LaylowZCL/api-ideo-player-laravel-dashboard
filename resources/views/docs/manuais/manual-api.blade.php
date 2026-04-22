@extends('layouts.app-docs')

@section('title', 'Manual de Utilização da API')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual de Utilização da API</h1>
  <p class="text-secondary">Referência funcional da API que suporta o dashboard web e a aplicação desktop.</p>

  <section class="doc-section">
    <h2>1. Papel da API na solução</h2>
    <p>A API é a camada que centraliza a comunicação entre os componentes da plataforma. Ela fornece dados ao dashboard, recebe pedidos do cliente desktop e mantém o fluxo de operação da solução.</p>
  </section>

  <section class="doc-section">
    <h2>2. O que a API suporta</h2>
    <ul>
      <li>autenticação e acesso aos serviços internos da solução;</li>
      <li>consulta de vídeos, agendamentos e dados operacionais;</li>
      <li>receção de relatórios e eventos provenientes dos clientes desktop;</li>
      <li>exposição de informação necessária à execução dos conteúdos.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>3. Requisitos gerais</h2>
    <ul>
      <li>Servidor compatível com Laravel e PHP em versão suportada.</li>
      <li>Base de dados relacional corretamente configurada.</li>
      <li>Servidor web configurado com HTTPS em produção.</li>
      <li>Permissões de escrita válidas para pastas de cache e logs.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>4. Configuração inicial</h2>
    <ol>
      <li>Definir as variáveis de ambiente no ficheiro <code>.env</code>.</li>
      <li>Configurar ligação à base de dados, URL da aplicação, email e parâmetros técnicos necessários.</li>
      <li>Executar as migrações e validar o acesso à base.</li>
      <li>Confirmar que a aplicação responde corretamente em ambiente local ou produtivo.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>5. Integração com a aplicação desktop</h2>
    <p>A aplicação desktop usa a API para consultar dados de operação e enviar eventos. Essa interação é técnica e faz parte do funcionamento interno da solução.</p>
    <ul>
      <li>O cliente desktop consulta a API para saber quais conteúdos executar e em que condições.</li>
      <li>A API devolve os dados necessários para essa execução.</li>
      <li>Após a execução, o cliente pode reportar eventos para telemetria, relatórios e auditoria.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>6. Boas práticas operacionais</h2>
    <ul>
      <li>Utilizar HTTPS em produção sem exceção.</li>
      <li>Restringir o acesso aos endpoints técnicos aos clientes autorizados.</li>
      <li>Monitorizar falhas de autenticação, erros de execução e degradação de desempenho.</li>
      <li>Garantir backups regulares da base de dados e capacidade de restauro.</li>
      <li>Manter logs e auditoria disponíveis para análise de incidentes.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>7. Publicação da documentação</h2>
    <p>A documentação oficial desta solução deve ser publicada e mantida sob o caminho:</p>
    <ul class="mb-0">
      <li><code>/docs</code></li>
      <li><code>/docs/&lt;manual&gt;</code></li>
    </ul>
  </section>
</main>
@endsection
