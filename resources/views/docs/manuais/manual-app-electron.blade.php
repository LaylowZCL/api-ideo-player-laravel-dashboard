@extends('layouts.app-docs')

@section('title', 'Manual de Utilização da Aplicação Desktop')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual de Utilização da Aplicação Desktop</h1>
  <p class="text-secondary">Orientações gerais para instalação, operação e validação do cliente desktop da solução.</p>

  <section class="doc-section">
    <h2>1. O que é a aplicação desktop</h2>
    <p>A aplicação desktop é o componente instalado nos postos finais para reproduzir os conteúdos geridos centralmente no dashboard. Ela funciona como cliente operacional da solução e depende da API para obter instruções e reportar eventos.</p>
  </section>

  <section class="doc-section">
    <h2>2. Plataformas suportadas</h2>
    <ul>
      <li><strong>Windows</strong></li>
      <li><strong>macOS</strong></li>
    </ul>
    <p>Os pacotes de instalação devem ser gerados especificamente para cada sistema operativo suportado.</p>
  </section>

  <section class="doc-section">
    <h2>3. Funcionamento geral</h2>
    <ol>
      <li>A aplicação inicia no posto final.</li>
      <li>Consulta a API para obter a configuração necessária.</li>
      <li>Identifica o conteúdo a executar com base nas regras definidas centralmente.</li>
      <li>Executa os vídeos e envia informação operacional para a plataforma.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>4. Antes de instalar ou compilar</h2>
    <ul>
      <li>Confirmar o endereço final da API que será utilizado.</li>
      <li>Garantir que o ambiente de destino consegue comunicar com a API.</li>
      <li>Validar que a configuração técnica do cliente aponta para o domínio correto.</li>
      <li>Assegurar que o pacote foi preparado para o sistema operativo certo.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>5. Instalação</h2>
    <h3>5.1 Windows</h3>
    <ol>
      <li>Executar o instalador fornecido para Windows.</li>
      <li>Seguir o assistente até ao final.</li>
      <li>Concluir a instalação e validar a primeira execução.</li>
    </ol>

    <h3>5.2 macOS</h3>
    <ol>
      <li>Abrir o pacote disponibilizado para macOS.</li>
      <li>Mover a aplicação para a pasta adequada.</li>
      <li>Autorizar a primeira execução, se o sistema solicitar.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>6. Validação após instalação</h2>
    <ol>
      <li>Confirmar que a aplicação abre corretamente.</li>
      <li>Verificar se consegue comunicar com a API.</li>
      <li>Validar se os conteúdos esperados são carregados.</li>
      <li>Confirmar se os eventos são refletidos no lado do dashboard ou relatórios.</li>
    </ol>
  </section>

  <section class="doc-section">
    <h2>7. Situações comuns a verificar</h2>
    <ul>
      <li><strong>A aplicação não recebe conteúdos:</strong> validar conectividade com a API e configuração do cliente.</li>
      <li><strong>Conteúdos não reproduzem:</strong> verificar se existem agendamentos ativos e conteúdos disponíveis.</li>
      <li><strong>Informação não aparece no dashboard:</strong> confirmar se os eventos estão a ser enviados corretamente.</li>
    </ul>
  </section>
</main>
@endsection
