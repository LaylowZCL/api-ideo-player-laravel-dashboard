# Fluxo de Importação e Armazenamento de "Registos Importados" AD

## 📊 Diagrama do Fluxo

```
┌─────────────────────────────────────────────────────────────────────┐
│                          FRONTEND (Vue.js)                          │
│  AdTargetsPage.vue - Card "Registos Importados" (Linha 122)        │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  [Botão "Actualizar"] @click="importAdFile()" (Linha 9)    │   │
│  └────────────────────┬──────────────────────────────────────┘   │
└─────────────────────────┼───────────────────────────────────────────┘
                          │ GET /actualizar-json
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    ROTA WEB (routes/web.php)                        │
│  Route::get('/actualizar-json') - Linha 229-254                    │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Middleware: auth, two_factor, can:isAdmin,               │   │
│  │             module.access:targets, throttle:10,1           │   │
│  │                                                             │   │
│  │  Artisan::call('ad:import-json')                           │   │
│  └────────────────────┬──────────────────────────────────────┘   │
└─────────────────────────┼───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│            COMANDO ARTISAN (Console/Commands)                       │
│  ImportAdGroupJson - app/Console/Commands/ImportAdGroupJson.php    │
│                                                                     │
│  Signature: ad:import-json {--path=}                               │
│  Agendamento: Diariamente às 01:00 (Console/Kernel.php)           │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  1. Resolve caminho do JSON:                               │   │
│  │     $jsonService->getAdImportPath()                         │   │
│  │     Ficheiro: mock-users.json                              │   │
│  │                                                             │   │
│  │  2. Lê e normaliza JSON                                    │   │
│  │     $jsonService->getTargetRecordsFromPath($path)          │   │
│  │                                                             │   │
│  │  3. Normaliza campos:                                      │   │
│  │     Maquina/maquina/machine → machine (lowercase)         │   │
│  │     Usuario/usuario/user → user (lowercase)               │   │
│  │     Nome/nome/name → user_display_name                    │   │
│  │     Email/email → user_email (lowercase)                  │   │
│  │     Grupo/grupo/group → group                             │   │
│  │     Data/date → effective_at                              │   │
│  │                                                             │   │
│  │  4. Processa dados:                                        │   │
│  │     - Agrupa por máquina                                  │   │
│  │     - Extrai grupos únicos                                │   │
│  │                                                             │   │
│  │  5. Sincroniza com BD (updateOrCreate)                    │   │
│  └────────────────────┬──────────────────────────────────────┘   │
└─────────────────────────┼───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│               SERVIÇO (Services/AdGroupJsonService.php)             │
│                                                                     │
│  Métodos-chave:                                                     │
│  • readJsonFile($path) → Lê arquivo JSON                           │
│  • normalizeJsonContents($contents) → Normaliza encoding            │
│    ├─ Remove BOM (UTF-8)                                            │
│    └─ Converte ISO-8859-1, Windows-1252 → UTF-8                    │
│  • normalizeTargetRecord($record) → Mapeia campos                   │
│  • getTargetRecordsFromPath($path) → Array de targets              │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│              BANCO DE DADOS - Tabelas Relacionadas                  │
│                                                                     │
│  ╔═════════════════════════════════════════════════════════════╗   │
│  ║         Tabela: ad_group_targets (Principal)               ║   │
│  ╠═════════════════════════════════════════════════════════════╣   │
│  ║ id                  │ INT PRIMARY KEY                       ║   │
│  ║ client_id           │ INT (FK → clients.id)                ║   │
│  ║ machine_name        │ VARCHAR (lowercase)                  ║   │
│  ║ user_name           │ VARCHAR (lowercase)                  ║   │
│  ║ user_display_name   │ VARCHAR (nome completo)             ║   │
│  ║ user_email          │ VARCHAR (lowercase)                  ║   │
│  ║ ad_group_id         │ INT (FK → ad_groups.id)             ║   │
│  ║ effective_at        │ TIMESTAMP                            ║   │
│  ║ source              │ VARCHAR = 'json'                     ║   │
│  ║ created_at          │ TIMESTAMP                            ║   │
│  ║ updated_at          │ TIMESTAMP                            ║   │
│  ╚═════════════════════════════════════════════════════════════╝   │
│                                                                     │
│  ╔═════════════════════════════════════════════════════════════╗   │
│  ║         Tabela: clients (Máquinas)                          ║   │
│  ╠═════════════════════════════════════════════════════════════╣   │
│  ║ Criada via: Client::firstOrCreate()                         ║   │
│  ║ client_id (unique) = nome máquina (e.g., DISPLAY001)       ║   │
│  ╚═════════════════════════════════════════════════════════════╝   │
│                                                                     │
│  ╔═════════════════════════════════════════════════════════════╗   │
│  ║         Tabela: ad_groups (Grupos AD)                       ║   │
│  ╠═════════════════════════════════════════════════════════════╣   │
│  ║ name (unique)   = Grupo (e.g., GRP_VIDEO)                  ║   │
│  ║ source          = 'ad'                                      ║   │
│  ║ active          = true                                      ║   │
│  ╚═════════════════════════════════════════════════════════════╝   │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│           ARQUIVO STATUS (storage/AD/import-status.json)            │
│                                                                     │
│  {                                                                   │
│    "last_import_at": "2024-04-15 10:30:45",                        │
│    "source_path": "/path/to/mock-users.json",                      │
│    "records": 250,                                                  │
│    "clients_created": 10,                                           │
│    "clients_updated": 5,                                            │
│    "groups_processed": 8,                                           │
│    "targets_processed": 250                                         │
│  }                                                                   │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     API PARA VISUALIZAÇÃO                           │
│  GET /api/ad/targets?machine=X&user=Y&group=Z&per_page=25         │
│  Controller: AdTargetController::index()                            │
│                                                                     │
│  Retorna registos de ad_group_targets com:                          │
│  - machine_name, user_name, user_display_name, user_email         │
│  - group (de ad_groups), effective_at, source                      │
└─────────────────────────┬───────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────────────┐
│                  FRONTEND - Visualização                            │
│  Tabela "Registos Importados" (AdTargetsPage.vue linha 122)        │
│                                                                     │
│  Colunas exibidas:                                                  │
│  • Máquina (machine_name)                                           │
│  • Utilizador (user_name)                                           │
│  • Nome (user_display_name)                                         │
│  • Email (user_email)                                               │
│  • Grupo (group)                                                    │
│  • Efetivo em (effective_at)                                        │
│  • Origem (source)                                                  │
│                                                                     │
│  Badge com total: {{ pagination.total }} itens                      │
└─────────────────────────────────────────────────────────────────────┘
```

## 📁 Ficheiro JSON de Entrada (mock-users.json)

**Localização**: Configurável via `config('ad.mock_users_path')`
- Procurado em: Raiz do projeto, `base_path()`, `storage_path()`

**Formato esperado**:
```json
[
  {
    "Maquina": "DISPLAY001",
    "Usuario": "joao.silva",
    "Nome": "João Silva",
    "Email": "joao.silva@example.com",
    "Grupo": "GRP_VIDEO",
    "Data": "2024-01-15"
  },
  {
    "machine": "DISPLAY002",
    "user": "maria.santos",
    "display_name": "Maria Santos",
    "email": "maria.santos@example.com",
    "group": "GRP_ADMIN",
    "date": "2024-01-15"
  }
]
```

**Campos aceitos** (Flexibilidade de nomes):
| Campo | Aliases Aceitos | Tipo | Descrição |
|-------|-----------------|------|-----------|
| Máquina | maquina, machine, Machine | string | Nome da máquina (requerido) |
| Utilizador | usuario, user, User, username, Username | string | Login do utilizador |
| Nome | nome, name, Name, display_name | string | Nome completo |
| Email | email, mail, Mail | string | Endereço de email |
| Grupo | grupo, group, Group | string | Grupo AD (requerido) |
| Data | data, Date, date | string | Data de efetividade |

**Processamento especial**:
- Todos os campos de texto são trimmed
- `machine`, `user` são convertidos para lowercase
- `email` é convertido para lowercase
- BOM UTF-8 é removido
- Encoding convertido de ISO-8859-1 ou Windows-1252 para UTF-8 se necessário

## 🔑 Chaves de API/Controllers

### Frontend - Componente Vue
📄 [resources/js/components/AdTargetsPage.vue](resources/js/components/AdTargetsPage.vue)

**Métodos importantes**:
- `importAdFile()` - Linha 358: Desencadeia importação via GET /actualizar-json
- `refresh()` - Recarrega dados após importação bem-sucedida
- `applyFilters()` - Filtra por máquina, utilizador, grupo

### Routes
📄 [routes/web.php](routes/web.php#L229-L254)

**Rota de atualização manual**:
```php
Route::get('/actualizar-json', function () {
    Artisan::call('ad:import-json');
    // Retorna status de sucesso/erro
})
```

### Command Artisan
📄 [app/Console/Commands/ImportAdGroupJson.php](app/Console/Commands/ImportAdGroupJson.php)

**Execução**:
```bash
# Manual
php artisan ad:import-json

# Com caminho customizado
php artisan ad:import-json --path=/custom/path/to/file.json

# Agendada (diariamente às 01:00)
# Configurada em app/Console/Kernel.php linha 16
```

### Service
📄 [app/Services/AdGroupJsonService.php](app/Services/AdGroupJsonService.php)

**Métodos público**:
- `getTargetRecords()` - Retorna array de registos normalizados
- `getAdImportPath()` - Retorna caminho resolvido do JSON
- `getGroupsForMachine($machine, $username)` - Grupos específicos de máquina/utilizador

### Models
📄 [app/Models/AdGroupTarget.php](app/Models/AdGroupTarget.php)

**Relacionamentos**:
- `adGroup()` - BelongsTo AdGroup
- `client()` - BelongsTo Client

### Controller API
📄 [app/Http/Controllers/AdTargetController.php](app/Http/Controllers/AdTargetController.php)

**Endpoint**: `/api/ad/targets`
**Filtros**: `machine`, `user`, `group`, `per_page`

## ⏰ Agendamento Automático

📄 [app/Console/Kernel.php](app/Console/Kernel.php#L16)

```php
$schedule->command('ad:import-json')->dailyAt('01:00');
```

A importação ocorre automaticamente todos os dias às 01:00 (1 da manhã).

## 🔐 Segurança e Permissões

**Acesso restrito a**:
- Usuários autenticados (`auth`)
- Com 2FA habilitado (`two_factor`)
- Com role de admin (`can:isAdmin`)
- Com acesso ao módulo "targets" (`module.access:targets`)
- Rate limit: 10 requisições por minuto (`throttle:10,1`)

## 📊 Sumário de Fluxo

| Etapa | Componente | Ação |
|-------|-----------|------|
| 1 | Frontend (Vue) | Clique botão "Actualizar" |
| 2 | Route Web | GET /actualizar-json |
| 3 | Command Artisan | ad:import-json executado |
| 4 | Service | Lê e normaliza JSON |
| 5 | Database | updateOrCreate em ad_group_targets |
| 6 | Storage | Grava import-status.json |
| 7 | API | GET /api/ad/targets |
| 8 | Frontend | Tabela "Registos Importados" atualizada |

---

**Última atualização**: Abril 2024  
**Versão**: v1.0
