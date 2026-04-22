@extends('layouts.app-docs')

@section('title', 'Manual de Utilização do Dashboard Web')

@section('content')
<main class="container py-4">
  <a href="{{ route('docs.index') }}" class="btn btn-outline-primary btn-sm mb-3">Voltar ao índice</a>
  <h1 class="h3">Manual de Utilização do Dashboard Web</h1>
  <p class="text-secondary">Guia prático para operadores, gestores e administradores da plataforma.</p>

  <section class="doc-section">
    <h2>1. Finalidade do dashboard</h2>
    <p>O dashboard web é o ponto central de gestão da solução. Nele, o utilizador pode administrar os conteúdos de vídeo, configurar agendamentos, acompanhar a operação, consultar relatórios e gerir entidades auxiliares conforme o perfil atribuído.</p>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/painel-de-controle.png') }}" alt="Painel principal do dashboard">
      <figcaption>Exemplo do painel principal com visão resumida do estado da operação.</figcaption>
    </figure>
  </section>

  <section class="doc-section">
    <h2>2. Acesso à plataforma</h2>
    <h3>2.1 Início de sessão</h3>
    <ol>
      <li>Aceda ao endereço oficial da aplicação no navegador.</li>
      <li>Introduza o seu nome de utilizador ou email.</li>
      <li>Introduza a sua palavra-passe.</li>
      <li>Clique em <strong>Entrar</strong>.</li>
    </ol>
    <p>Se tiver permissões válidas, será encaminhado para o dashboard ou para etapas adicionais de segurança, como alteração de palavra-passe no primeiro acesso ou validação de segundo fator.</p>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/login.png') }}" alt="Ecrã de login">
      <figcaption>Ecrã de autenticação da aplicação.</figcaption>
    </figure>

    <h3>2.2 Primeiro acesso e segurança</h3>
    <ul>
      <li><strong>Primeiro acesso:</strong> alguns utilizadores podem ser obrigados a alterar a palavra-passe antes de continuar.</li>
      <li><strong>2FA:</strong> dependendo da configuração da organização, poderá ser necessário validar um código adicional.</li>
      <li><strong>Permissões:</strong> após o login, as secções disponíveis no menu dependem do seu perfil e permissões.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>3. Navegação principal</h2>
    <p>O menu lateral organiza as funcionalidades disponíveis. Os itens mais comuns são:</p>
    <ul>
      <li><strong>Painel:</strong> visão geral do estado da plataforma.</li>
      <li><strong>Agendamentos:</strong> gestão dos horários de reprodução.</li>
      <li><strong>Vídeos:</strong> carregamento, pesquisa e manutenção dos conteúdos.</li>
      <li><strong>Relatórios:</strong> histórico operacional e indicadores de execução.</li>
      <li><strong>Minha Conta:</strong> atualização de dados do próprio utilizador.</li>
      <li><strong>Utilizadores / Administração:</strong> gestão avançada disponível apenas para perfis autorizados.</li>
    </ul>
    <p>Se uma opção não estiver visível, isso normalmente significa que o seu perfil não tem permissão para essa área.</p>
  </section>

  <section class="doc-section">
    <h2>4. Painel principal</h2>
    <p>O painel apresenta uma leitura rápida do estado do sistema. Nesta área, o utilizador consegue perceber, sem navegar por todas as secções, se o sistema está operacional, quantos vídeos e agendamentos existem e quais os pontos que merecem atenção.</p>
    <ul>
      <li>Use o painel para confirmar se a operação está estável.</li>
      <li>Verifique indicadores que apontem para falhas de reprodução, ausência de conteúdos ou necessidade de sincronização.</li>
      <li>Utilize os atalhos do painel para aceder rapidamente às áreas mais usadas.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>5. Gestão de vídeos</h2>
    <p>A secção <strong>Vídeos</strong> é usada para manter o catálogo de conteúdos que poderão ser reproduzidos nos clientes.</p>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/carregamento-de-video.png') }}" alt="Gestão de vídeos">
      <figcaption>Área de gestão de vídeos com ações de pesquisa, sincronização e manutenção.</figcaption>
    </figure>

    <h3>5.1 O que pode fazer nesta área</h3>
    <div class="table-responsive">
      <table class="table table-sm table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Ação</th>
            <th>Quando usar</th>
            <th>Resultado esperado</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Sincronizar API</strong></td>
            <td>Quando precisar reconfirmar o estado dos conteúdos e dos metadados.</td>
            <td>A lista é atualizada com informação consistente e sem duplicações desnecessárias.</td>
          </tr>
          <tr>
            <td><strong>Upload manual</strong></td>
            <td>Quando for necessário adicionar um novo vídeo ao sistema.</td>
            <td>O vídeo passa a estar disponível para utilização em agendamentos.</td>
          </tr>
          <tr>
            <td><strong>Pesquisar e filtrar</strong></td>
            <td>Quando a lista estiver extensa ou precisar encontrar um vídeo específico.</td>
            <td>A interface mostra apenas os itens relevantes.</td>
          </tr>
          <tr>
            <td><strong>Editar</strong></td>
            <td>Quando precisar corrigir ou atualizar dados do vídeo.</td>
            <td>As alterações ficam refletidas na listagem.</td>
          </tr>
          <tr>
            <td><strong>Eliminar</strong></td>
            <td>Quando um conteúdo já não deve permanecer disponível.</td>
            <td>O item é removido após confirmação.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <h3>5.2 Como carregar um vídeo</h3>
    <ol>
      <li>Clique em <strong>Upload manual</strong>.</li>
      <li>Selecione o ficheiro de vídeo pretendido.</li>
      <li>Preencha ou confirme os dados apresentados no formulário.</li>
      <li>Grave a operação.</li>
      <li>Valide se o vídeo aparece na listagem e se está apto para uso.</li>
    </ol>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/carregamento-de-video.png') }}" alt="Carregamento de vídeo">
      <figcaption>Janela de carregamento de novos conteúdos de vídeo.</figcaption>
    </figure>

    <h3>5.3 Boas práticas ao gerir vídeos</h3>
    <ul>
      <li>Utilize títulos claros e consistentes para facilitar a pesquisa.</li>
      <li>Evite duplicar o mesmo conteúdo com nomes diferentes.</li>
      <li>Depois de alterações relevantes, confirme se os agendamentos continuam corretos.</li>
      <li>Antes de eliminar um vídeo, verifique se ele não está associado a agendamentos ativos.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>6. Gestão de agendamentos</h2>
    <p>A secção <strong>Agendamentos</strong> define quando e onde cada vídeo será executado.</p>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/agendamentos.png') }}" alt="Lista de agendamentos">
      <figcaption>Área de listagem e gestão dos agendamentos configurados.</figcaption>
    </figure>

    <h3>6.1 Ações disponíveis</h3>
    <ul>
      <li><strong>Novo agendamento:</strong> cria um registo novo com vídeo, horário e demais parâmetros.</li>
      <li><strong>Editar:</strong> ajusta os dados de um agendamento existente.</li>
      <li><strong>Duplicar:</strong> cria rapidamente uma cópia para pequenos ajustes.</li>
      <li><strong>Ativar/Desativar:</strong> controla se o agendamento está em vigor.</li>
      <li><strong>Eliminar:</strong> remove definitivamente o registo.</li>
    </ul>

    <h3>6.2 Como criar um agendamento</h3>
    <ol>
      <li>Clique em <strong>Novo agendamento</strong>.</li>
      <li>Escolha o vídeo que pretende reproduzir.</li>
      <li>Defina o título do agendamento.</li>
      <li>Configure hora, dias e demais parâmetros necessários.</li>
      <li>Grave o registo.</li>
      <li>Confirme se ele surge corretamente na lista de agendamentos.</li>
    </ol>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/criar-novo-agendamento.png') }}" alt="Criar novo agendamento">
      <figcaption>Formulário de criação e edição de agendamentos.</figcaption>
    </figure>

    <h3>6.3 Cuidados importantes</h3>
    <ul>
      <li>Revise os horários antes de gravar para evitar conflitos operacionais.</li>
      <li>Ao duplicar, confirme sempre se o novo horário e os dias estão corretos.</li>
      <li>Desativar é preferível a eliminar quando existe hipótese de reutilização futura.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>7. Gestão de utilizadores</h2>
    <p>A secção <strong>Utilizadores</strong> destina-se à administração de contas e permissões de acesso.</p>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/utilizadores.png') }}" alt="Gestão de utilizadores">
      <figcaption>Listagem de utilizadores com ações de administração e manutenção.</figcaption>
    </figure>

    <h3>7.1 O que pode ser feito</h3>
    <ul>
      <li>Criar novos utilizadores.</li>
      <li>Atualizar dados e perfil de acesso.</li>
      <li>Definir permissões compatíveis com a função do colaborador.</li>
      <li>Desativar ou remover contas, de acordo com a política interna.</li>
    </ul>

    <h3>7.2 Criar um utilizador</h3>
    <ol>
      <li>Clique em <strong>Criar utilizador</strong> ou ação equivalente.</li>
      <li>Preencha nome, email, nome de utilizador e perfil.</li>
      <li>Selecione as permissões necessárias.</li>
      <li>Grave a operação e confirme a disponibilidade da nova conta.</li>
    </ol>
    <figure class="doc-figure">
      <img src="{{ asset('docs-assets/images/dashboard/criar-usuario.png') }}" alt="Criar utilizador">
      <figcaption>Exemplo do formulário de criação de utilizador.</figcaption>
    </figure>

    <h3>7.3 Regras gerais de permissões</h3>
    <ul>
      <li>Os administradores têm acesso mais amplo às áreas do sistema.</li>
      <li>Gestores podem ter acesso intermédio, conforme a configuração.</li>
      <li>Operadores e utilizadores com perfil reduzido veem apenas o que é necessário para o seu trabalho.</li>
    </ul>
  </section>

  <section class="doc-section">
    <h2>8. Administração complementar</h2>
    <p>Dependendo do perfil do utilizador, existem ainda áreas adicionais de administração:</p>
    <ul>
      <li><strong>Clientes:</strong> gestão de clientes ou postos associados à operação.</li>
      <li><strong>Grupos:</strong> organização de entidades ou agrupamentos relevantes para a lógica do sistema.</li>
      <li><strong>Campanhas:</strong> gestão de conjuntos de conteúdos ou contextos de execução.</li>
      <li><strong>Alvos AD:</strong> integração e monitorização de alvos ligados ao Active Directory.</li>
      <li><strong>Configurações:</strong> parâmetros globais da aplicação.</li>
    </ul>
    <div class="row g-3">
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/clientes.png') }}" alt="Gestão de clientes">
          <figcaption>Área de clientes.</figcaption>
        </figure>
      </div>
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/campanhas.png') }}" alt="Gestão de campanhas">
          <figcaption>Área de campanhas.</figcaption>
        </figure>
      </div>
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/grupos.png') }}" alt="Gestão de grupos">
          <figcaption>Área de grupos.</figcaption>
        </figure>
      </div>
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/alvos-ad.png') }}" alt="Alvos AD">
          <figcaption>Área de alvos AD.</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="doc-section">
    <h2>9. Relatórios, logs e auditoria</h2>
    <p>As áreas de <strong>Relatórios</strong> e <strong>Logs</strong> servem para acompanhar a execução da solução e investigar comportamentos fora do esperado.</p>
    <ul>
      <li>Use relatórios para analisar eventos de reprodução, estados e resultados da operação.</li>
      <li>Use logs para auditoria e investigação de falhas técnicas ou operacionais.</li>
      <li>Procure sinais de erro, interrupções de playback e ausência de conteúdos esperados.</li>
    </ul>
    <div class="row g-3">
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/relatorios.png') }}" alt="Relatórios">
          <figcaption>Área de relatórios operacionais.</figcaption>
        </figure>
      </div>
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/logs-e-auditoria.png') }}" alt="Logs e auditoria">
          <figcaption>Registos de logs e auditoria para análise e suporte.</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="doc-section">
    <h2>10. Minha conta e configurações</h2>
    <p>Na secção <strong>Minha Conta</strong>, cada utilizador pode atualizar os seus dados pessoais e, quando permitido, alterar a sua palavra-passe. Já a secção <strong>Configurações</strong> concentra parâmetros globais acessíveis apenas a perfis autorizados.</p>
    <div class="row g-3">
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/minha-conta.png') }}" alt="Minha conta">
          <figcaption>Área para gestão do perfil do utilizador autenticado.</figcaption>
        </figure>
      </div>
      <div class="col-md-6">
        <figure class="doc-figure h-100">
          <img src="{{ asset('docs-assets/images/dashboard/configuracoes.png') }}" alt="Configurações">
          <figcaption>Área de configurações e parâmetros gerais da plataforma.</figcaption>
        </figure>
      </div>
    </div>
  </section>

  <section class="doc-section">
    <h2>11. Dúvidas frequentes</h2>
    <h3>11.1 Não vejo uma opção no menu</h3>
    <p>Provavelmente o seu perfil não possui permissão para essa secção. Contacte um administrador da plataforma.</p>

    <h3>11.2 O vídeo não aparece num agendamento</h3>
    <p>Confirme se o vídeo foi carregado corretamente, se está disponível e se o agendamento foi gravado com sucesso.</p>

    <h3>11.3 Um utilizador não consegue entrar</h3>
    <p>Verifique credenciais, estado da conta, exigência de primeiro acesso, obrigatoriedade de 2FA e eventual bloqueio administrativo.</p>

    <h3>11.4 Os relatórios mostram falhas de reprodução</h3>
    <p>Use a área de logs e relatórios para identificar o tipo de erro, o cliente afetado e o momento da ocorrência, e depois valide a configuração do conteúdo ou do agendamento associado.</p>
  </section>
</main>
@endsection
