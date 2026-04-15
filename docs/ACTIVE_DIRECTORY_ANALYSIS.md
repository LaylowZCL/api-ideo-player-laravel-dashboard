# Análise do Active Directory (AD) na Aplicação

## 🔐 Funcionalidades Principais do AD

### 1. Autenticação de Utilizadores
- **Login via AD**: Utilizadores fazem login com credenciais do Active Directory
- **SSO (Single Sign-On)**: Suporte para login automático via header HTTP
- **Fallback Local**: Se AD falhar, pode usar autenticação local (configurável)

### 2. Gestão de Grupos e Permissões
- **Mapeamento de Grupos AD**: Grupos do AD são sincronizados com o sistema
- **Hierarquia de Papéis**:
  - `super_admin` → Grupo `AD_GROUP_SUPER_ADMIN`
  - `admin` → Grupo `AD_GROUP_ADMIN` 
  - `manager` → Grupo `AD_GROUP_MANAGER`

### 3. Targeting de Conteúdo
- **AdGroupTargets**: Associa utilizadores/máquinas a grupos específicos
- **Controle de Acesso**: Vídeos podem ser direcionados a grupos específicos
- **Resolução Dinâmica**: Determina que conteúdo mostrar com base no utilizador/máquina

## 🛠️ Componentes Técnicos

### Serviços Principais
- **`ActiveDirectoryService`**: Conexão LDAP com servidor AD
- **`AdGroupJsonService`**: Importação/exportação de configurações via JSON
- **`TargetResolverService`**: Resolve permissões com base em LDAP/JSON

### Configurações Chave
```php
'enabled' => env('AD_ENABLED', false),
'dashboard_uses_ad' => $dashboardUsesAd,
'allow_local_fallback' => env('AD_ALLOW_LOCAL_FALLBACK', true),
'host' => env('AD_HOST', ''),
'base_dn' => env('AD_BASE_DN', ''),
'group_source' => env('AD_GROUP_SOURCE', 'ldap'), // ldap, json, hybrid
```

### Modos de Operação
- **LDAP**: Pura integração com Active Directory
- **JSON**: Configuração manual via ficheiros JSON
- **Hybrid**: Tenta LDAP primeiro, fallback para JSON

## 🎯 Para que Serve?

1. **Ambientes Corporativos**: Integra com infraestrutura Windows existente
2. **Gestão Centralizada**: Utiliza AD existente para gestão de acessos
3. **Controle Granular**: Vídeos direcionados por departamento/cargo
4. **Segurança**: Aproveita políticas de segurança do AD
5. **Flexibilidade**: Suporta tanto AD como configuração manual

## 📊 Fluxo de Funcionamento

1. **Login** → Validação no AD via LDAP
2. **Sincronização** → Grupos AD importados para base de dados
3. **Targeting** → Conteúdo direcionado com base nos grupos
4. **Fallback** → Se AD indisponível, usa autenticação local

## 🗂️ Estrutura de Dados

### Tabelas Principais
- **`ad_groups`**: Grupos sincronizados do AD
- **`ad_group_targets`**: Associação utilizador/máquina → grupo
- **`client_ad_group`**: Associação cliente → grupo
- **`schedule_ad_group`**: Associação agendamento → grupo

### Modelos Relacionados
- **`AdGroup`**: Modelo para grupos do AD
- **`AdGroupTarget`**: Modelo para targets de usuários/máquinas

## 🔧 Configuração via .env

```bash
# Active Directory Configuration
AD_ENABLED=true
AD_ALLOW_LOCAL_FALLBACK=true
AD_HOST=dc01.bancomoc.local
AD_PORT=636
AD_USE_SSL=true
AD_USE_TLS=false
AD_REQUIRE_SSL=true
AD_BASE_DN=DC=bancomoc,DC=local
AD_BIND_DN="CN=svc-ldap-video,OU=Service Accounts,DC=bancomoc,DC=local"
AD_BIND_PASSWORD="password"
AD_USER_ATTRIBUTE=sAMAccountName
AD_GROUP_ATTRIBUTE=memberOf

# Group Mapping
AD_GROUP_SUPER_ADMIN=GRP_ROLE_SUPER_ADMIN
AD_GROUP_ADMIN=GRP_ROLE_ADMIN
AD_GROUP_MANAGER=GRP_ROLE_MANAGER

# Configuration Source
AD_GROUP_SOURCE=ldap  # ldap, json, hybrid
AD_SYNC_CLIENT_GROUPS=true
AD_GROUP_JSON_PATH=storage/app/AD/ad-groups.json

# SSO Configuration
AD_SSO_ENABLED=true
AD_SSO_HEADER=REMOTE_USER
AD_MOCK_ONLY=false
```

## 🚀 Comandos Disponíveis

```bash
# Importar grupos AD a partir de JSON
php artisan ad:import-json

# Criar superadmin (se AD estiver desativado)
php artisan make:superadmin email@example.com --password="password"
```

## 📝 Considerações

- **Segurança**: Utiliza LDAP sobre SSL/TLS para conexões seguras
- **Performance**: Cache de grupos para reduzir consultas LDAP
- **Flexibilidade**: Suporta múltiplos modos de operação
- **Resiliência**: Fallback para autenticação local se AD falhar

É um sistema robusto para ambientes empresariais que utilizam Active Directory para gestão de identidade e controle de acesso a conteúdo multimídia.
