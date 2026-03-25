# Analise completa da aplicacao (para IA)

## 1. Visao geral
Aplicacao web Laravel com painel de controle (dashboard) e front-end em Vue 3 + Vite para gerenciar videos, agendamentos e configuracoes de um player (inclui fluxo para app Electron). Possui dois conjuntos de APIs:
- Rotas web com prefixo `/api` (em `routes/web.php`) usadas pelo painel Vue.
- Rotas API publicas em `routes/api.php` usadas pelo cliente Electron (protegidas por API key).

Objetivo funcional principal:
- Cadastrar, sincronizar e cachear videos.
- Agendar exibicao de videos por dia e horario.
- Reportar eventos de reproducao (telemetria).
- Monitorar clientes online (heartbeat).
- Configurar parametros do sistema e do player.

## 2. Stack e dependencias
Backend:
- Laravel 10, PHP 8.1, MySQL.
- Auth web com Laravel UI (rotas de login/registro).
- Sanctum instalado (apenas rota `/api/user` usa).
- php-ffmpeg para deteccao de duracao de video.

Frontend:
- Vue 3, Vite, Bootstrap 5, Axios.
- Chart.js para graficos no dashboard.

Outros:
- Uso de `ffprobe` e `ffmpeg` via CLI quando disponiveis no SO.

Arquivos chave:
- `composer.json`
- `package.json`

## 3. Arquitetura de alto nivel
Camadas principais:
- Rotas web e API em `routes/web.php` e `routes/api.php`.
- Controllers em `app/Http/Controllers`.
- Models Eloquent em `app/Models`.
- Migrations em `database/migrations`.
- Front-end em `resources/js` e `resources/views`.

Fluxos basicos:
- Usuario autenticado acessa paginas Blade (dashboard, videos, schedules, logs, settings, users).
- Essas paginas montam componentes Vue (SPA parcial) que consomem `/api/*` em `routes/web.php`.
- App Electron consome `/api/*` em `routes/api.php` com API key.

## 4. Rotas e endpoints
### 4.1 Rotas web (UI + API interna)
Arquivo: `routes/web.php`

Views:
- `/dashboard` -> `DashboardController@dashboard`
- `/video` -> `VideoController@goToVideos`
- `/schedule` -> `ScheduleController@goToSchedule`
- `/logs` -> `LogController@goToLogs`
- `/preview` -> `PreviewController@goToPreview`
- `/settings` -> `SettingController@goToSettings`
- `/users` -> `UserController@goToUsers`

API interna (prefixo `/api`, middleware `auth` + `internal.api`):
- `/api/current-user` (inline closure)
- `/api/dashboard/data` -> `DashboardController@getDashboardData`
- `/api/videos/*` -> `VideoController` (listagem, upload, sync, download, update, delete)
- `/api/schedules/*` -> `ScheduleController` (CRUD, toggle, duplicate, hoje, player)
- `/api/users/*` -> `UserController` (CRUD)
- `/api/settings` -> `SettingController@index/store` (settings antiga)
- `/api/system-settings/*` -> `SystemSettingController` (settings nova, test, restore, history, export)
- `/api/client/*` -> `ClientMonitorController` (stats/online)
- `/api/admin/clients` -> `ClientMonitorController@dashboard`

Outras:
- `/clear-all` -> limpa caches, requer `auth` e `is_admin`.

### 4.2 Rotas API publicas (Electron)
Arquivo: `routes/api.php`

Publicas:
- `/api/health` -> status simples.

Protegidas por `api.auth`:
- `/api/schedules/clients` -> `ClientAppController@schedules`
- `/api/scheduled/videos` -> `ClientAppController@scheduledVideos`
- `/api/videos/report` -> `ClientAppController@storeReport`
- `/api/ping` -> `ClientMonitorController@ping`

## 5. Controllers e regras de negocio
### 5.1 Videos
Arquivo: `app/Http/Controllers/VideoController.php`

Funcoes principais:
- Listar videos com filtro e estatisticas (cache, tamanho, status API).
- Sincronizar videos locais em `public/videos` com tabela `videos`.
- Upload manual para `public/videos`.
- Download de video via URL externa e cache em `storage/app/public`.
- Remover video do cache local e deletar registro.

Detalhes tecnicos:
- Deteccao de duracao via `ffprobe` CLI, fallback `php-ffmpeg`, fallback `ffmpeg` CLI.
- Detecao de status da API via endpoints configurados em `config/services.php` e `SystemSetting`.

### 5.2 Schedules (Agendamentos)
Arquivo: `app/Http/Controllers/ScheduleController.php`

Funcoes:
- CRUD de agendamentos (dias da semana, horario, monitor, ativo).
- Duplicar e alternar status.
- Retornar agendamentos do dia e para player externo.

### 5.3 Dashboard
Arquivo: `app/Http/Controllers/DashboardController.php`

Funcoes:
- Dados agregados para cards e graficos.
- Estatisticas de `VideoReport` (visualizacoes, conclusoes, sessoes).
- Logs recentes e proximos agendamentos.

### 5.4 Report de videos
Arquivo: `app/Http/Controllers/ClientAppController.php`

Funcoes:
- Reportar eventos de playback (playback_started, completed, etc) com device info e session id.
- Agendamentos para o cliente Electron.

### 5.5 Monitoramento de clientes
Arquivo: `app/Http/Controllers/ClientMonitorController.php`

Funcoes:
- `ping` recebe heartbeat e atualiza cache de clientes.
- `stats` e `online` leem cache.
- `dashboard` mostra view simples de clientes online.

Implementacao em:
- `app/Providers/ClientMonitorServiceProvider.php` (singleton com cache, cleanup diario).

### 5.6 Configuracoes
Existem duas camadas:
- `SettingController` + `settings` table (mais antigo).
- `SystemSettingController` + `system_settings` (novo, com historico).

## 6. Modelo de dados
Tabelas principais:
- `videos`: metadados, cache local, URL, duracao, status.
- `schedules`: titulo, video_url, horario, dias, monitor, ativo.
- `video_reports`: eventos de playback, device info, session id, viewed_at.
- `settings` e `system_settings`: configuracoes do player/sistema.
- `logs`: eventos simples do painel.
- `users`: usuarios com `user_type`.

Migrations relevantes:
- `2025_07_08_000002_create_videos_table.php`
- `2025_07_08_000001_create_schedules_table.php`
- `2025_12_06_100121_create_video_reports_table.php`
- `2025_12_04_202210_create_system_settings_table.php`

## 7. Autenticacao e autorizacao
- Auth web padrao (Laravel UI) para acessar o painel.
- Middleware `internal.api` bloqueia chamadas internas que nao tenham header de Vue/AJAX.
- Middleware `api.auth` protege rotas do Electron via API key.
- Gates definidos em `AuthServiceProvider` usando `user_type` (admin, manager, user).

## 8. Front-end
- Paginas Blade montam componentes Vue: `resources/views/*.blade.php`.
- Componentes em `resources/js/components` (Dashboard, Videos, Schedules, Logs, Preview, Settings, Users).
- Axios usado para consumir `/api/*` com headers AJAX.
- Chart.js usado no dashboard.

## 9. Observabilidade e logs
- Logs de eventos simples na tabela `logs`.
- Logs de erros com `Log::error` em controllers.
- Relatorios detalhados em `video_reports`.

## 10. Cache e storage
- Videos podem ficar em:
  - `public/videos` (upload local e sync de arquivos).
  - `storage/app/public/videos` (download via API externa).
- Client monitor usa `Cache` (chave `video_clients`), expira em 24h e renova a cada heartbeat.

## 11. Testes
- `tests/Feature/VideoUploadTest.php`: testa upload de videos e validacao de mime.
- Sem testes extensivos para schedules, users, system settings e API key.

## 12. Riscos e inconsistencias detectadas
1) Inconsistencia entre `settings` table e `Setting` model.
- `Setting` model nao possui fillable para colunas reais da tabela `settings`.
- Resultado: updates podem nao persistir corretamente.

2) `system_settings` migration nao contem campos `api_endpoint`, `api_key`, `sync_interval`.
- `SystemSetting` model e controller dependem desses campos.
- Provavel mismatch de schema.

3) `videos.size` e `Video` model.
- Migration define `size` como string; model converte para integer.
- Pode gerar dados inconsistentes e formatacoes incorretas.

4) `internal.api` nao e seguranca real.
- Baseado em headers que podem ser falsificados.
- Endpoints internos ficam expostos se a app estiver publica.

5) Inconsistencia entre `user_type` e colunas `is_admin`/`is_manager`.
- Gates usam `user_type`, mas rota `/clear-all` verifica `is_admin`.
- Pode bloquear admins ou permitir inconsistencias.

6) Caminho de arquivos de video inconsistente.
- Upload local usa `public/videos`, enquanto download usa `storage/app/public`.
- `getScheduleForPlayer` tenta `asset('storage/...')`, o que nao encontra videos em `public/videos`.

7) Validacao de API key basica.
- API key estatica por config e `SystemSetting`.
- Sem rotacao, rate limit ou expiração.

## 13. Melhorias recomendadas (alto nivel)
- Unificar modelo de configuracoes (usar apenas `system_settings`).
- Corrigir migrations para alinhar com models e controllers.
- Reforcar protecao de endpoints internos (auth + policy, ou signed requests).
- Padronizar armazenamento de video (sempre `storage/app/public` com symlink).
- Adicionar testes de feature para schedules, users e reports.
- Documentar contrato das APIs (Swagger/OpenAPI).

## 14. Mapa rapido de arquivos
Backend:
- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/*`
- `app/Models/*`
- `app/Http/Middleware/InternalApiAccess.php`
- `app/Http/Middleware/ApiAuthMiddleware.php`

Frontend:
- `resources/js/app.js`
- `resources/js/components/*.vue`
- `resources/views/*.blade.php`

Infra:
- `config/services.php`
- `config/app.php`
- `composer.json`
- `package.json`

