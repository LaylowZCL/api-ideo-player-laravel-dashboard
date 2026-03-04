# Requisitos de Alojamento - BM Video Schedule Manager

## 1. Objetivo
Este documento define os requisitos de infraestrutura e operação para alojar a aplicação **BM Video Schedule Manager** em:
- **Servidor:** Rocky Linux
- **Base de dados:** PostgreSQL

A solução inclui:
- Backend/API Laravel 10 (PHP 8.1+)
- Frontend Vue 3 (build via Vite)
- Área de documentação web
- Upload, armazenamento e gestão de vídeos

## 2. Requisitos de Infraestrutura (mínimos recomendados)
### 2.1 Ambiente mínimo (produção inicial)
- CPU: 4 vCPU
- RAM: 8 GB
- Disco: 120 GB SSD (se houver muitos vídeos, aumentar)
- Rede: IP público fixo + domínio DNS

### 2.2 Ambiente recomendado (produção estável)
- CPU: 8 vCPU
- RAM: 16 GB
- Disco: 250+ GB SSD/NVMe
- Volume separado para vídeos e backups

## 3. Sistema Operativo e Pacotes Base
- Rocky Linux 9.x (recomendado)
- `firewalld` ativo
- `SELinux` em modo Enforcing (com políticas ajustadas)
- `git`, `curl`, `wget`, `unzip`, `tar`
- `supervisor` (ou systemd units equivalentes para workers)
- `certbot` (ou outro cliente ACME para TLS)

## 4. Stack de Aplicação
### 4.1 PHP
- PHP **8.1+** (ideal: 8.2)
- PHP-FPM
- Extensões obrigatórias:
  - `php-cli`
  - `php-fpm`
  - `php-mbstring`
  - `php-xml`
  - `php-curl`
  - `php-zip`
  - `php-gd`
  - `php-bcmath`
  - `php-fileinfo`
  - `php-opcache`
  - `php-pdo`
  - `php-pgsql`

### 4.2 Servidor web
- Nginx (recomendado) ou Apache
- Document root deve apontar para: `.../public`
- HTTPS obrigatório (TLS 1.2+)

### 4.3 Node.js (build de frontend)
- Node.js 20 LTS (recomendado)
- npm 10+
- Necessário para `npm install` e `npm run build`

### 4.4 Composer
- Composer 2.x

### 4.5 Banco de Dados
- PostgreSQL 14+ (recomendado 15/16)
- Encoding `UTF8`
- Timezone coerente com operação (recomendado UTC no servidor)

### 4.6 FFmpeg
A aplicação usa `php-ffmpeg/php-ffmpeg` e fallback com comando de sistema para duração de vídeos.

Instalar:
- `ffmpeg`
- `ffprobe`

Observação:
- `shell_exec` não deve estar bloqueado no `php.ini` para o fallback funcionar.

## 5. Requisitos de Rede e Portas
Abrir externamente apenas:
- `443/TCP` (HTTPS)
- `80/TCP` (opcional, só para redirecionamento para 443)

Acesso administrativo:
- `22/TCP` (SSH), com whitelist de IP e autenticação por chave

Não expor publicamente:
- PostgreSQL (`5432`)
- Redis (`6379`), se usado
- portas de desenvolvimento (ex.: `8081`)

## 6. Requisitos de Segurança
- TLS válido (Let's Encrypt ou certificado corporativo)
- `APP_DEBUG=false` em produção
- Rotação e proteção de segredos (`APP_KEY`, chaves internas)
- Cabeçalhos de segurança no servidor web (HSTS, X-Frame-Options, etc.)
- Rate limit ativo nas rotas críticas
- Privilégios mínimos no utilizador PostgreSQL da aplicação
- Backups cifrados e com retenção

## 7. Requisitos de Configuração da App (.env)
Obrigatórios:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://SEU_DOMINIO`
- `DB_CONNECTION=pgsql`
- `DB_HOST=...`
- `DB_PORT=5432`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

Internos da solução:
- `APP_API_KEY=...`
- `APP_CLIENT_ID=ELECTRON_VIDEO_PLAYER` (ou valor definido pela operação)
- `VIDEO_API_BASE_URL=https://SEU_DOMINIO/api`
- `VIDEO_API_ENDPOINT=https://SEU_DOMINIO/api/videos`

Recomendados:
- `LOG_CHANNEL=stack`
- `LOG_LEVEL=info`
- `CACHE_DRIVER=file` (ou redis)
- `SESSION_DRIVER=file` (ou redis/database)
- `QUEUE_CONNECTION=database` (ou redis) para maior robustez

## 8. Requisitos de Deploy
Passos mínimos:
1. Clonar o repositório no servidor.
2. Instalar dependências PHP: `composer install --no-dev --optimize-autoloader`
3. Instalar dependências frontend: `npm install`
4. Gerar build: `npm run build`
5. Configurar `.env` de produção.
6. Gerar chave: `php artisan key:generate`
7. Executar migrações: `php artisan migrate --force`
8. Criar link de storage: `php artisan storage:link`
9. Otimizar: `php artisan optimize`

Permissões:
- Utilizador do servidor web deve ter escrita em:
  - `storage/`
  - `bootstrap/cache/`

## 9. Serviços em Produção
### 9.1 Processos essenciais
- Nginx/Apache
- PHP-FPM
- PostgreSQL

### 9.2 Agendador Laravel
Adicionar no cron (utilizador da app):
- `* * * * * php /caminho/da/app/artisan schedule:run >> /dev/null 2>&1`

### 9.3 Filas (se `QUEUE_CONNECTION` não for `sync`)
- Worker Laravel em `supervisor` ou `systemd`
- Reinício automático em falhas

## 10. Base de Dados PostgreSQL - Requisitos
- Criar base e utilizador dedicados para a aplicação
- Privilégios mínimos necessários
- Ativar backups automáticos diários
- Política de retenção recomendada: 30 a 90 dias
- Teste periódico de restauro (mínimo trimestral)

## 11. Armazenamento de Vídeos
- Definir caminho persistente para uploads/cache de vídeos
- Monitorizar crescimento de disco
- Política de limpeza de ficheiros obsoletos
- Considerar volume dedicado quando crescimento for alto

## 12. Monitorização e Operação
Monitorizar no mínimo:
- disponibilidade HTTP (200/5xx)
- uso de CPU/RAM/disco
- erro de PHP-FPM/Nginx
- logs Laravel (`storage/logs`)
- espaço em disco do diretório de vídeos

Alertas recomendados:
- CPU > 85%
- RAM > 85%
- disco > 80%
- aumento anormal de 5xx

## 13. Checklist de Go-Live
- [ ] DNS apontado para o servidor
- [ ] HTTPS válido e redirecionamento 80 -> 443
- [ ] `.env` de produção configurado
- [ ] PostgreSQL acessível pela app
- [ ] `php artisan migrate --force` executado
- [ ] `npm run build` executado
- [ ] permissões corretas em `storage/` e `bootstrap/cache/`
- [ ] cron de `schedule:run` ativo
- [ ] backup automático ativo
- [ ] monitorização e alertas ativos
- [ ] teste de upload/reprodução de vídeo validado
- [ ] teste de autenticação API (X-API-Key / X-Client-ID) validado

## 14. Repositórios de Referência da Solução
- Laravel + Vue: https://github.com/LaylowZCL/api-ideo-player-laravel-dashboard
- Electron: https://github.com/LaylowZCL/popup-video-player-with-Scheduler
