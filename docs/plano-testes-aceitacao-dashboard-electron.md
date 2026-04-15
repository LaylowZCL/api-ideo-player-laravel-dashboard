# Proposta de Plano de Testes de Aceitação

## 1. Identificação

- Documento: Proposta de Plano de Testes de Aceitação
- Solução: BM Video Schedule Manager
- Componentes avaliados: 1. Dashboard Web/API Laravel e 2. Aplicação Desktop Electron
- Base de avaliação: ficheiro `Criterios de Avaliação_GINÁSTICA LABORAL_2026.xlsx`
- Versão do documento: 1.0
- Data: 2026-04-08
- Estado: Proposta para homologação

## 2. Objetivo

Definir o plano de testes de aceitação da solução mista Dashboard + Electron, alinhando a homologação aos 10 critérios do documento de avaliação fornecido. O foco deste plano é comprovar, com evidências objetivas, que a solução atende aos requisitos funcionais, operacionais, de segurança e de compatibilidade exigidos.

## 3. Escopo

### Em escopo

- Gestão de vídeos no Dashboard.
- Gestão de agendamentos no Dashboard.
- Gestão de utilizadores, perfis e permissões.
- Configuração de segurança e autenticação web.
- Integração Dashboard/API com o cliente Electron.
- Reprodução de vídeos em pop-up no Electron.
- Telemetria, heartbeat e relatórios de reprodução.
- Segmentação por clientes alvo e grupos.
- Documentação técnica e operacional associada.

### Fora de escopo

- Testes de carga, stress e volume em escala massiva.
- Pentest formal de infraestrutura.
- Testes de DRP, backup e restore em produção.
- Certificação de assinatura de instaladores por entidade externa.

## 4. Referências do projeto

- `README.md`
- `resources/views/documentacao/manuais/manual-dashboard-web.blade.php`
- `resources/views/documentacao/manuais/manual-app-electron.blade.php`
- `resources/views/documentacao/manuais/manual-solucao-mista.blade.php`
- `resources/views/documentacao/manuais/ficha-tecnica.blade.php`
- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/ClientAppController.php`
- `app/Http/Controllers/ClientMonitorController.php`
- `app/Models/Schedule.php`
- `app/Models/Video.php`

## 5. Estratégia de testes

Os testes de aceitação serão executados em duas frentes complementares:

1. Validação funcional do Dashboard Web, cobrindo cadastro, configuração, segurança, segmentação, operação e auditoria.
2. Validação operacional do Electron, cobrindo instalação, compatibilidade Windows/macOS, reprodução em pop-up, comandos, legendas, som, always on top e comunicação com a API.

### Tipos de teste considerados

- Testes funcionais end-to-end.
- Testes de integração Dashboard/API/Electron.
- Testes de compatibilidade Windows e macOS.
- Testes de segurança funcional.
- Testes de usabilidade operacional para operadores e supervisores.
- Testes documentais e de evidência.

### Criticidade

- Alta: critérios 1, 2, 3, 5, 6, 8 e 9.
- Média: critérios 4, 7 e 10.

## 6. Ambientes e pré-requisitos

### Ambiente mínimo de homologação

- 1 servidor com Dashboard/API configurado em HTTPS.
- 1 base de dados homologada.
- 1 posto Windows 10 ou 11.
- 1 posto macOS Intel ou Apple Silicon.
- Acesso à documentação técnica publicada.
- Conta de operador.
- Conta de supervisor ou administrador.

### Dados de teste mínimos

- 3 vídeos válidos em MP4.
- 1 vídeo com legenda `.srt`.
- 1 vídeo sem áudio.
- 1 vídeo com áudio ativo.
- 2 grupos alvo distintos.
- 2 clientes/hosts distintos associados a grupos diferentes.
- 2 utilizadores com perfis diferentes.

### Dependências

- API disponível e autenticável.
- Chave técnica do Electron válida.
- Endpoint `/api/health` operacional.
- Endpoints `/api/schedules/clients`, `/api/scheduled/videos`, `/api/subtitles/{schedule_id}`, `/api/videos/report` e `/api/ping` acessíveis.

## 7. Critérios de entrada e saída

### Entrada

- Build do Dashboard publicado em homologação.
- Build do Electron gerado para Windows e macOS.
- Domínio final da API definido no Electron.
- Massa de teste preparada.
- Utilizadores e grupos alvo configurados.

### Saída

- 100% dos testes críticos executados.
- 0 falhas abertas de severidade alta.
- Falhas médias apenas com mitigação aprovada.
- Evidências anexadas para cada critério avaliado.
- Aprovação formal de homologação emitida pela área avaliadora.

## 8. Matriz de rastreabilidade dos critérios

| Critério | Descrição resumida | Componente principal | Evidência esperada |
| --- | --- | --- | --- |
| 1 | Pop-up always on top | Electron | Vídeo, screenshot e validação de posição/tamanho |
| 2 | Comandos play/repeat/pause/volume/maximize | Electron | Execução assistida dos comandos |
| 3 | Suporte Windows e macOS | Electron | Instalação e execução em ambas as plataformas |
| 4 | Adição de legendas | Dashboard + Electron | Upload/configuração e renderização da legenda |
| 5 | Ativar/desabilitar som | Dashboard + Electron | Teste de áudio ativo e mutado |
| 6 | Seleção de clientes alvos | Dashboard + API + Electron | Publicação segmentada por grupos/clientes |
| 7 | Recursos de hardware e software | Documentação + operação | Ficha técnica, manuais e sessão de apresentação |
| 8 | Segurança da solução | Dashboard + API | Perfis, MFA, HTTPS, logs e auditoria |
| 9 | Agendamento de vídeos | Dashboard + Electron | Agendamento criado e executado no cliente alvo |
| 10 | Inserir títulos e subtítulos | Dashboard + Electron | Metadados visíveis e consistentes |

## 9. Cenários de aceitação por critério

### Critério 1. Pop-ups always on top

- Validar que o Electron abre o pop-up no canto inferior direito.
- Validar que o tamanho da janela respeita a configuração acordada.
- Validar que a janela permanece sobre outras aplicações comuns do posto.
- Validar comportamento após minimizar, alternar foco e reabrir reprodução.

Resultado esperado:
O pop-up abre em posição e dimensão corretas e mantém prioridade visual sem perder estabilidade.

### Critério 2. Comandos play, repeat, pause, volume e maximize

- Reproduzir um vídeo agendado no Electron.
- Acionar `play/pause` e confirmar alteração imediata do estado.
- Acionar `repeat` e confirmar reinício correto do conteúdo.
- Acionar controlo de volume e confirmar ajuste audível.
- Acionar `maximize` e confirmar redimensionamento sem perda funcional.

Resultado esperado:
Todos os comandos existem, respondem sem erro e afetam o player conforme esperado.

### Critério 3. Suporte Windows e macOS

- Instalar o build Windows em equipamento homologado.
- Instalar o build macOS em equipamento homologado.
- Validar arranque da aplicação, conectividade com a API e reprodução.
- Validar envio de heartbeat e relatórios em ambas as plataformas.

Resultado esperado:
A solução instala, inicia, autentica e reproduz corretamente em Windows e macOS.

### Critério 4. Adição de legendas

- Carregar ou associar vídeo com legenda.
- Validar resposta do endpoint de subtítulos.
- Executar vídeo no Electron e confirmar exibição da legenda.
- Validar legibilidade, sincronismo básico e sizing da fonte.

Resultado esperado:
Legendas são aceitas, disponibilizadas pela API e renderizadas no cliente com legibilidade adequada.

### Critério 5. Ativação ou desabilitação de som

- Reproduzir vídeo com áudio ativo.
- Validar saída sonora normal.
- Acionar opção de mutar ou desativar som.
- Confirmar ausência de áudio sem quebrar a reprodução.
- Se aplicável, repetir com áudio masculino e feminino previstos na avaliação.

Resultado esperado:
O áudio pode ser ativado/desativado de forma controlada e persistente durante a execução.

### Critério 6. Seleção de clientes alvos

- Configurar grupos e clientes alvo no Dashboard.
- Associar agendamento ou campanha a grupos/clientes específicos.
- Validar no Electron do alvo A o recebimento do vídeo correto.
- Validar no Electron do alvo B o recebimento de vídeo diferente ou ausência do conteúdo não destinado.
- Confirmar integração com grupos oriundos de AD ou fonte equivalente configurada.

Resultado esperado:
A publicação é segmentada corretamente por cliente ou grupo, sem vazamento entre públicos.

### Critério 7. Recursos de hardware e software envolvidos

- Apresentar ficha técnica da solução.
- Validar disponibilidade de documentação com componentes, portas, protocolos e sistema de ficheiros.
- Confirmar manual do Dashboard, manual do Electron e manual da solução mista.
- Validar realização de sessão de formação para operadores.
- Validar realização de sessão de formação para supervisores.

Resultado esperado:
Os recursos técnicos e operacionais da solução estão documentados e apresentados de forma suficiente para operação e suporte.

### Critério 8. Segurança da solução

- Validar criação e gestão de perfis de utilizador.
- Validar restrição por permissões e módulos.
- Validar autenticação web e, quando ativo, MFA/2FA.
- Validar notificação por e-mail em fluxos de senha previstos.
- Validar uso de HTTPS/TLS em homologação.
- Validar autenticação técnica do Electron via chave e client ID.
- Validar existência de relatórios/logs de auditoria e telemetria.

Resultado esperado:
A solução protege acessos, segrega permissões, transmite dados com segurança e mantém trilha de auditoria.

### Critério 9. Agendamento dos vídeos

- Criar agendamento no Dashboard com título, vídeo, hora, dias e alvo.
- Ativar o agendamento.
- Confirmar leitura do horário pelo Electron.
- Validar execução automática no horário previsto.
- Validar geração de relatório de reprodução ou adesão.

Resultado esperado:
O agendamento é persistido, distribuído ao cliente correto e executado automaticamente com evidência registrada.

### Critério 10. Inserção de títulos e subtítulos

- Criar ou editar vídeo com título e subtítulo/descrição aplicáveis.
- Validar exibição correta no Dashboard.
- Validar disponibilidade dos dados no fluxo entregue ao Electron.
- Confirmar consistência dos metadados em relatórios ou tela de operação.

Resultado esperado:
Os vídeos mantêm título e subtítulos/metadados de forma consistente entre cadastro, distribuição e monitorização.

## 10. Casos prioritários por componente

### Dashboard Web/API

- Autenticação do utilizador e, quando aplicável, desafio 2FA.
- Cadastro, edição, pesquisa e remoção de vídeos.
- Upload manual e sincronização de vídeos.
- Criação, edição, duplicação, ativação e eliminação de agendamentos.
- Gestão de utilizadores por perfil.
- Configuração de grupos, clientes, campanhas e alvos.
- Consulta de logs, relatórios e monitorização de clientes online.
- Exportação de relatórios.

### Electron

- Instalação e arranque em Windows.
- Instalação e arranque em macOS.
- Conectividade com a API configurada.
- Leitura de agenda e seleção do próximo vídeo.
- Reprodução do pop-up com `always on top`.
- Execução dos comandos do player.
- Exibição de legendas.
- Mutar/reativar som.
- Envio de `ping` e `videos/report`.

## 11. Evidências obrigatórias

- Checklist assinado por critério.
- Capturas de ecrã do Dashboard.
- Gravação de ecrã do Electron em Windows e macOS.
- Export de relatórios ou logs relevantes.
- Registo de utilizadores/perfis testados.
- Registo dos grupos/clientes alvo utilizados.
- Resultado final consolidado por critério: aprovado, aprovado com ressalvas ou reprovado.

## 12. Severidade e gestão de defeitos

- Crítica: impede instalação, autenticação, reprodução, segurança ou agendamento.
- Alta: quebra critério principal, mas há contorno parcial.
- Média: impacto moderado, sem bloquear homologação integral.
- Baixa: detalhe visual, textual ou documental sem impacto funcional relevante.

Cada defeito deve conter:

- Título e severidade.
- Ambiente e versão testada.
- Passos para reproduzir.
- Resultado esperado e resultado obtido.
- Evidência anexa.
- Responsável e estado.

## 13. Gate de aprovação

A solução será considerada aprovada quando:

1. Todos os critérios 1 a 10 tiverem evidência objetiva de execução.
2. Todos os cenários críticos estiverem aprovados em pelo menos um posto Windows e um posto macOS.
3. Não existirem defeitos críticos ou altos sem mitigação formal.
4. A documentação técnica e operacional estiver entregue.
5. A comissão avaliadora confirmar aderência aos pesos e subitens do ficheiro de avaliação.

## 14. Riscos e observações

- Parte dos testes do Electron depende do binário final e do repositório desktop separado.
- A validação de integração com Active Directory depende da disponibilidade do ambiente configurado.
- A validação de HTTPS, certificados e notificações por e-mail deve ocorrer em ambiente de homologação configurado e não apenas local.
- O critério 7 inclui componente documental e de formação, portanto não deve ser tratado apenas como teste técnico.

## 15. Recomendação de execução

Recomenda-se executar a aceitação em quatro blocos:

1. Preparação do ambiente e massa.
2. Testes funcionais do Dashboard.
3. Testes operacionais do Electron em Windows e macOS.
4. Consolidação documental, evidências e parecer final.

## 16. Parecer proposto

Esta proposta cobre integralmente os 10 critérios identificados no anexo e traduz cada item em validações observáveis, evidências e gate de aprovação. Para a homologação formal, o próximo passo recomendado é derivar deste plano uma matriz executiva com colunas de `ID do teste`, `pré-condição`, `passos`, `resultado esperado`, `evidência`, `executor`, `data` e `status`.
