# Teste de Active Directory em localhost

## Objetivo
Permitir testes locais sem acesso ao Domain Controller real.

## Configuração recomendada
No `.env` local:

```
BM_DASHBOARD_AUTH=false
AD_ENABLED=true
AD_GROUP_SOURCE=json
AD_GROUP_JSON_PATH=storage/app/ad/ad-groups.json
AD_MOCK_USERS_PATH=storage/app/ad/mock-users.json
TWO_FACTOR_ENABLED=true
```

## Arquivo de grupos (exemplo)
Crie `storage/app/ad/ad-groups.json` com o formato:

```
[
  {
    "Maquina": "DISPLAY001",
    "Usuario": "joao",
    "Grupo": "GRP_VIDEO_MANHA",
    "Data": "2026-03-19 10:00:00"
  },
  {
    "Maquina": "DISPLAY001",
    "Usuario": "joao",
    "Grupo": "GRP_VIDEO_TARDE",
    "Data": "2026-03-19 10:00:00"
  }
]
```

## Usuários mock (exemplo)
Crie `storage/app/ad/mock-users.json`:

```
[
  {
    "username": "joao",
    "password": "senha123",
    "name": "João Mock",
    "email": "joao@local.ad",
    "groups": ["GRP_VIDEO_MANHA"]
  }
]
```

## Importação manual
```
php artisan ad:import-json
```

Isso cria/atualiza clientes e associa grupos conforme o JSON diário.
