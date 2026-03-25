# Relatorio Atual - Autenticacao, Controle de Acessos, Distribuicao e Alvos

Data: 2026-03-20
Projeto(s): API Laravel + Electron (popup-video-player-with-Scheduler)

## 1) Autenticacao (API/Dashboard)

### Modo atual
- Autenticacao via Active Directory (AD) com suporte a modo mock/local.
- AD local (on-premise) com LDAPS.
- SSO habilitado por header (configuravel).
- 2FA TOTP ativo e integrado com Google Authenticator.

### Fluxos implementados
- Login:
  - Se AD habilitado:
    - SSO: usa header `AD_SSO_HEADER` (ex: `REMOTE_USER`) para login automatico.
    - Caso sem SSO: autentica via AD (LDAP bind) ou via mock JSON quando `AD_MOCK_ONLY=true`.
  - Se AD desabilitado: login local padrao (email + password).
- 2FA:
  - Setup gera secret TOTP e otpauth.
  - Verificacao com tolerancia de tempo (janela de 2 passos).
  - Recovery codes gerados ao ativar.
  - QR Code exibido na tela para Google Authenticator.

### Configuracoes relevantes (.env)
- `AD_ENABLED=true`
- `AD_USE_SSL=true`
- `AD_PORT=636`
- `AD_REQUIRE_SSL=true`
- `AD_GROUP_SOURCE=json` (para grupos do JSON diario)
- `AD_MOCK_ONLY=true` (no ambiente local para evitar bind LDAP)
- `AD_SSO_ENABLED=true`
- `AD_SSO_HEADER=REMOTE_USER`
- `TWO_FACTOR_ENABLED=true`
- `TWO_FACTOR_REQUIRED=true`

### Observacao
- Com `AD_MOCK_ONLY=true`, o LDAP nao e utilizado e os usuarios sao lidos de `storage/app/ad/mock-users.json`.
- O login foi validado com usuario mock `joao/senha123`.

## 2) Controle de Acessos (Roles/Permissoes)

### Modelo de roles
- Roles de usuario:
  - `super_admin`
  - `admin`
  - `manager`
  - `user`
- Mapeamento automatico por grupos AD:
  - `AD_GROUP_SUPER_ADMIN`
  - `AD_GROUP_ADMIN`
  - `AD_GROUP_MANAGER`
- Ao autenticar via AD/mock:
  - Usuario e sincronizado no banco.
  - Role atribuido com base nos grupos AD.

### Restricoes
- Logs e administracao de logs acessiveis apenas a `admin` e `super_admin`.
- Middleware 2FA bloqueia acesso ate concluir desafio.

## 3) Distribuicao de Videos (API)

### Regras de distribuicao
As agendas (schedules) sao filtradas com base em grupos e client_id:
- Alvos podem vir de:
  - JSON diario do AD (maquina/usuario/grupo).
  - Grupos do client (relacao `client_ad_group`).
  - Fallback quando nao ha grupos.

### Logica de selecao
Prioridade atual:
1. Usuario (quando `username` enviado).
2. Maquina (quando so `client_id`/hostname).
3. Grupos do client vinculados no banco.

### Endpoints relevantes
- `GET /api/scheduled/videos` (lista)
- `GET /api/scheduled/videos/next` (proximo video)
- `GET /api/dashboard` (config popup + stats)

## 4) Deteccao de Maquinas/Grupos Alvos

### Fonte principal (JSON diario)
O JSON diario esperado:
```
[
  {"Maquina":"PC001","Usuario":"joao","Grupo":"GRP_VIDEO_MANHA","Data":"2026-03-20 08:00:00"}
]
```

### Persistencia
- Importacao automatica para:
  - Tabela `ad_group_targets`
  - Tabela `client_ad_group` (sincronizacao opcional)

### Tabela `ad_group_targets`
Campos principais:
- `machine_name`
- `username`
- `group_name`
- `recorded_at`

### Servico de resolucao
`AdTargetingService` resolve grupos por:
- username (se fornecido)
- machine_name
- fallback para grupos do client

## 5) Electron (popup-video-player-with-Scheduler)

### Integracao com API
O cliente Electron:
- Faz `ping` na API enviando:
  - `client_id` = hostname
  - `username` = usuario do SO (via `X-User` e param)
- Busca configs de popup em `/api/dashboard`
- Faz cache local de `popup-settings.json` (fallback offline)
- Usa endpoint `/scheduled/videos/next` primeiro e depois fallback para `/scheduled/videos`

### Resiliencia offline
Se a API estiver indisponivel:
- Usa cache local de settings.
- Usa defaults do cliente.

## 6) Logs e Auditoria

### Eventos registrados
Todos os eventos do dashboard sao logados, incluindo:
- login (sucesso e falha)
- criacao/edicao/remocao de videos
- campanhas, horarios, grupos

### Acesso aos logs
Restrito a `admin` ou `super_admin`.

## 7) Pontos de Atencao

- Confirmar ambiente AD real (LDAPS e base DN reais).
- Validar o JSON diario entregue pelo Banco (campos e data).
- Ajustar `AD_GROUP_*` conforme DN dos grupos de producao.
- Garantir sincronizacao diaria do JSON (cron ou job).

## 8) Conclusao

O sistema esta pronto para:
- Autenticacao AD on-premise com fallback local/mock.
- Distribuicao de videos baseada em grupos AD e targets diarios.
- Cliente Electron sincronizado com API e fallback offline.

Para producao:
- Desativar `AD_MOCK_ONLY`.
- Configurar AD real (host/base DN/credenciais).
- Garantir rotina diaria de atualizacao do JSON do Banco.

## 9) Anexos Tecnicos

### 9.1 Endpoints (API)
- Autenticacao / 2FA (web):
  - `GET /login`
  - `POST /login`
  - `GET /two-factor/setup`
  - `POST /two-factor/setup`
  - `GET /two-factor/challenge`
  - `POST /two-factor/challenge`
  - `POST /two-factor/disable`
- Alvos e AD:
  - `GET /api/ad/health`
  - `GET /api/ad/json-status`
  - `GET /api/ad-targets`
- Videos / Agendamentos:
  - `GET /api/scheduled/videos`
  - `GET /api/scheduled/videos/next`
  - `GET /api/dashboard`

### 9.2 Fluxos (Resumo)
- Login com AD:
  - (Opcional) SSO por header -> lookup user -> sync user -> login.
  - Caso sem SSO -> authenticate AD/mock -> sync user -> login.
  - 2FA requerido -> setup/challenge antes de liberar acesso.
- Distribuicao de videos:
  - Electron envia `client_id` + `username`.
  - API resolve grupos via `AdTargetingService`.
  - Filtro de schedules por grupos e regras de campanha.

### 9.3 Modelos e Tabelas
- `users`:
  - `two_factor_secret`
  - `two_factor_recovery_codes`
  - `two_factor_confirmed_at`
  - flags `is_admin`, `is_manager`, `is_superadmin`
- `ad_group_targets`:
  - `machine_name`
  - `username`
  - `group_name`
  - `recorded_at`
- `client_ad_group`:
  - vincula `clients` aos grupos AD sincronizados

### 9.4 Configuracoes (AD/2FA)
- AD:
  - `AD_ENABLED`, `AD_HOST`, `AD_PORT`, `AD_USE_SSL`, `AD_REQUIRE_SSL`
  - `AD_BASE_DN`, `AD_BIND_DN`, `AD_BIND_PASSWORD`
  - `AD_GROUP_SOURCE`, `AD_GROUP_JSON_PATH`, `AD_MOCK_USERS_PATH`
  - `AD_SSO_ENABLED`, `AD_SSO_HEADER`
  - `AD_MOCK_ONLY` (apenas local)
- 2FA:
  - `TWO_FACTOR_ENABLED`, `TWO_FACTOR_REQUIRED`
  - `TWO_FACTOR_API_ENABLED`, `TWO_FACTOR_API_SECRET`

