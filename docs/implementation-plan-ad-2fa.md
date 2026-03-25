# Plano de Implementação — 2FA + Active Directory (LDAPS) + Seletor de Autenticação

Data: 2026-03-19

## Objetivo
Implementar 2FA na API, integrar autenticação via Active Directory (LDAPS) e permitir escolher entre autenticação atual (Laravel UI) e AD com base em variáveis de ambiente (`AD_ENABLED` ou `BM_DASHBOARD_AUTH`). Também estudar e implementar distribuição de vídeos baseada em grupos do AD, conforme o documento fornecido.

## Contexto do AD (extraído do documento)
- AD local (on‑premise)
- Integração via LDAPS
- Máquinas no domínio; distribuição de conteúdo baseada em grupos do AD
- Banco disponibiliza ficheiro JSON diário com mapeamento de `Maquina`, `Usuario`, `Grupo`, `Data`
- SSO desejado
- 2FA obrigatório, mas parametrizável (ativar/desativar)

## Escopo
1. **2FA na API** (configurável): endpoints do Electron e/ou dashboard API interna.
2. **Autenticação AD/SSO** para o dashboard (web).
3. **Seletor de autenticação** por variável de ambiente.
4. **Distribuição de vídeos por grupos do AD** (via JSON diário).
5. **Documentação e migrações necessárias**.

## Inventário Atual (alto nível)
- Autenticação web: Laravel UI (rotas padrão de login/registro)
- API pública do Electron: protegida por `api.auth` com API key
- Sanctum instalado, mas uso pontual
- Sem 2FA no código atual

## Plano — Fases e Tarefas

### Fase 1 — Diagnóstico e desenho técnico
1. Mapear fluxos de autenticação atuais (dashboard + API Electron).
2. Definir o alvo do 2FA:
   - 2FA para login no dashboard?
   - 2FA para API Electron (tokens de dispositivo)?
3. Confirmar variáveis de ambiente e precedência:
   - `BM_DASHBOARD_AUTH=true|false` (true = Laravel UI; false = AD)
   - `AD_ENABLED=true|false` (mantido por compatibilidade)
   - Regra proposta: `BM_DASHBOARD_AUTH` tem prioridade; se ausente usa `AD_ENABLED`.

### Fase 2 — Implementar seletor de autenticação
1. Criar configuração `config/auth_selector.php`.
2. Middleware global para redirecionar login conforme env.
3. Ajustar rotas de auth:
   - Se AD ativo, desabilitar registro/senha local.
   - Se Laravel UI ativo, fluxo atual permanece.
4. Documentar no `.env.example`.

### Fase 3 — Autenticação Active Directory (LDAPS)
1. Adicionar pacote LDAP (ex.: `directorytree/ldaprecord-laravel` ou `adldap2/adldap2-laravel`).
2. Configurar conexão LDAPS por env:
   - `AD_HOST`, `AD_PORT=636`, `AD_BASE_DN`, `AD_BIND_DN`, `AD_BIND_PASSWORD`, `AD_SSL=true`.
3. Implementar login AD:
   - Login via username/password (SSO “simples”, não Kerberos).
   - Mapeamento para usuário local (criar/atualizar usuário no `users`).
   - Sincronizar atributos básicos (nome, email, grupos).
4. Política de autorização por grupos:
   - Converter grupos do AD → roles (`user_type`).

### Fase 4 — 2FA (configurável)
1. Implementar TOTP para dashboard (ex.: Google Authenticator):
   - Novas colunas no `users`: `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`.
   - Fluxo: login → desafio 2FA (se habilitado e configurado).
2. Variável de ambiente:
   - `TWO_FACTOR_ENABLED=true|false`
3. Aplicar 2FA também em APIs sensíveis (se exigido):
   - Opção A: token device + refresh com 2FA no dashboard
   - Opção B: endpoint de validação 2FA antes de permitir operações críticas
4. UI de ativação/desativação 2FA no dashboard para administradores.

### Fase 5 — Distribuição de vídeos por grupos AD (JSON diário)
1. Criar ingestão diária do JSON (cron/command):
   - Ex.: `php artisan ad:import-groups`.
   - Armazenar em tabela `ad_group_mappings`:
     - `machine`, `username`, `group`, `data_at`.
2. Relacionar grupos AD a vídeos/schedules:
   - Nova tabela pivot `schedule_group` (schedule_id, group_name).
3. Ajustar endpoints do Electron:
   - Ao retornar vídeos/schedules, filtrar pelo grupo do usuário/máquina.
4. Adicionar tela no dashboard para seleção de grupos por schedule.

### Fase 6 — Testes, segurança e documentação
1. Testes de autenticação:
   - Laravel UI
   - AD login
   - 2FA
2. Testes de distribuição por grupo.
3. Hardening:
   - Garantir LDAPS obrigatório
   - Sanear headers e payloads
4. Atualizar documentação `README.md` e `docs`.

## Entregáveis
- Código com seletor de auth via `.env`
- Integração AD/LDAPS funcional
- 2FA configurável
- Distribuição por grupos AD
- Migrações e documentação

## Perguntas de validação antes da execução
1. 2FA deve ser apenas para login no dashboard ou também obrigatório para ações críticas da API?
2. O mapeamento de grupos AD → `user_type` precisa de regras específicas?
3. O JSON diário vem por upload manual, API ou arquivo em diretório?

---

Se estiver de acordo com este plano, prossigo para a implementação.
