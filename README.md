# VideoScheduler

A comprehensive Laravel-based dashboard for managing video schedules, playback, and Electron desktop client integration.

## Features

- **Video Management**: Upload, sync, cache, and organize video content
- **Schedule Management**: Create flexible playback schedules with day/time specifications
- **Client Monitoring**: Track online/offline status of Electron client applications
- **Telemetry Tracking**: Monitor video playback events and viewer statistics
- **System Settings**: Centralized configuration with version history
- **API Documentation**: Complete REST API specification for integrations
- **Role-Based Access**: Super Admin, Admin, Manager, and User role levels

## Prerequisites

- PHP >= 8.1
- Composer >= 2.0
- Node.js >= 14
- npm >= 6
- MySQL >= 5.7
- FFmpeg (for video duration detection)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd api-video-player-laravel-dashboard
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
npm run dev
```

### 4. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=video_scheduler
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

This creates all necessary tables including:
- `users` - User accounts with role management
- `videos` - Video metadata and storage info
- `schedules` - Video playback schedules
- `video_reports` - Playback telemetry
- `system_settings` - Application configuration
- `logs` - Event logs

### 6. Create Storage Symlink

```bash
php artisan storage:link
```

This creates a symlink from `public/storage` to `storage/app/public` for serving videos.

### 7. Serve the Application

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

---

## Post-Installation: Create Super Admin User

After installation, create your first super admin user to access the dashboard:

### Option 1: Use Artisan Command (Recommended)

```bash
php artisan make:superadmin admin@example.com
```

This will prompt you to:
1. Enter the user's name
2. Enter and confirm the password

Example:

```
$ php artisan make:superadmin admin@example.com
Enter the name for this super admin user [Super Admin]: John Admin
Enter password for the new super admin user: 
Confirm password: 

✓ Super admin user created successfully!

User Details:
  ID: 1
  Name: John Admin
  Email: admin@example.com
  Type: super_admin
  Super Admin: Yes

The user can now log in with these credentials.
```

### Option 2: Promote Existing User

If a user already exists, promote them to super admin:

```bash
php artisan make:superadmin existing@user.com
```

This will upgrade their role instead of creating a new user.

### Option 3: Manual Database Entry

As a last resort, create the user directly:

```bash
php artisan tinker

User::create([
    'name' => 'Super Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
    'user_type' => 'super_admin',
    'is_superadmin' => true,
    'email_verified_at' => now(),
]);
```

---

## Usage

### Dashboard Access

1. Navigate to `http://localhost:8000`
2. Login with your super admin credentials created above
3. Access the following sections:
   - **Dashboard**: View statistics, recent logs, and upcoming schedules
   - **Videos**: Upload, sync, and manage video content
   - **Schedules**: Create and manage video playback schedules
   - **Settings**: Configure system settings and API endpoint
   - **Users**: Manage user accounts and roles
   - **Logs**: View system event logs
   - **Client Monitor**: Track connected Electron clients

### Video Management

#### Upload Videos
1. Go to Videos section
2. Click "Upload Video"
3. Select file and enter title/description
4. Videos are stored in `storage/app/public/videos`

#### Sync Local Videos
1. Place MP4 files in `storage/app/public/videos`
2. Click "Sync" in Videos section
3. System automatically detects new videos and creates records

#### Video URLs
All videos are served from: `http://localhost:8000/storage/videos/{filename.mp4}`

### Schedule Management

1. Go to Schedules section
2. Click "Create Schedule"
3. Select video, set days/time, assign monitor
4. Activate schedule for Electron clients

### User Management

#### Creating Users

---

## Active Directory + 2FA

### Seleção de autenticação
- `BM_DASHBOARD_AUTH=true` usa Laravel UI (login local).
- `BM_DASHBOARD_AUTH=false` usa Active Directory (LDAPS).
- Se `BM_DASHBOARD_AUTH` não existir, usa `AD_ENABLED`.

### 2FA
Configuração via `.env`:
- `TWO_FACTOR_ENABLED=true|false`
- `TWO_FACTOR_REQUIRED=true|false`
- `TWO_FACTOR_API_ENABLED=true|false` (2FA na API via header `X-2FA-Code`)

### Teste local de AD
Veja o guia em `docs/ad-local-testing.md`.

1. Go to Users section
2. Click "Add User"
3. Select role:
   - **Super Admin** (`super_admin`): Full system access, can manage all users and settings
   - **Admin** (`admin`): Can manage videos, schedules, and regular users
   - **Manager** (`manager`): Can view and manage videos and schedules
   - **User** (`user`): View-only access

#### Role Hierarchy

```
Super Admin (highest privileges)
    ↓
Admin
    ↓
Manager
    ↓
User (view-only)
```

### Electron Client Integration

Configure your Electron client with:

1. **API Endpoint**: `http://server-ip:8000/api`
2. **API Key**: Found in Settings > System Settings > API Key
3. **Client ID**: `ELECTRON_VIDEO_PLAYER` (default)
4. **Heartbeat Interval**: 60 seconds (configurable)

See [Electron Client API Guide](docs/ELECTRON_CLIENT_API.md) for detailed implementation instructions.

---

## Configuration

### System Settings

Access system settings via Dashboard > Settings:

- **API Configuration**
  - `api_endpoint`: Server endpoint URL
  - `api_key`: Authentication key for Electron clients
  - `sync_interval`: Schedule sync frequency (seconds)

- **Display Settings**
  - `default_monitor`: Default display output
  - `always_on_top`: Keep window on top
  - `auto_close_delay`: Auto-close video after delay (0 = manual)

- **System Settings**
  - `start_with_windows`: Auto-start on system boot
  - `show_in_system_tray`: Show tray icon
  - `enable_notifications`: Desktop notifications

- **Cache & Storage**
  - `cache_location`: Video cache directory
  - `max_cache_size`: Maximum cache size (GB)
  - `auto_cleanup`: Auto-delete old videos

- **Performance**
  - `enable_hardware_acceleration`: GPU acceleration
  - `preload_videos`: Pre-buffer next video
  - `log_level`: Logging level (error/warning/info/debug)

---

## API Documentation

### Dashboard API

All dashboard endpoints require authenticated session. Access via `/api/*` routes.

**Example** - Get current user:
```bash
curl -b cookies.txt http://localhost:8000/api/current-user
```

**Example** - Get videos:
```bash
curl -b cookies.txt http://localhost:8000/api/videos
```

See [API Specification](docs/API_SPECIFICATION.md) for complete endpoint documentation.

### Client API

Public API for Electron clients. Requires API key authentication.

**Example** - Get schedules:
```bash
curl -H "X-API-Key: YOUR_API_KEY" \
     -H "X-Client-ID: ELECTRON_VIDEO_PLAYER" \
     http://localhost:8000/api/schedules/clients
```

**Example** - Send heartbeat:
```bash
curl -X POST \
     -H "X-API-Key: YOUR_API_KEY" \
     -H "X-Client-ID: ELECTRON_VIDEO_PLAYER" \
     -H "Content-Type: application/json" \
     -d '{"client_id":"CLIENT_001","device_name":"Monitor 1","status":"online"}' \
     http://localhost:8000/api/ping
```

See [API Specification](docs/API_SPECIFICATION.md) and [Electron Client API Guide](docs/ELECTRON_CLIENT_API.md) for complete documentation.

---

## Directory Structure

```
├── app/
│   ├── Console/Commands/
│   │   └── MakeSuperAdmin.php          # Super admin creation command
│   ├── Http/
│   │   ├── Controllers/                 # API and view controllers
│   │   ├── Middleware/                  # Auth and access middleware
│   │   └── Kernel.php
│   ├── Models/                          # Eloquent models
│   └── Providers/                       # Service providers
├── database/
│   ├── migrations/                      # Database schemas
│   ├── seeders/                         # Database seeders
│   └── factories/                       # Model factories for testing
├── docs/
│   ├── API_SPECIFICATION.md             # Complete API documentation
│   └── ELECTRON_CLIENT_API.md           # Electron client guide
├── public/
│   └── storage/                         # Symlink to storage/app/public
├── resources/
│   ├── js/                              # Vue.js components
│   ├── views/                           # Blade templates
│   └── css/                             # Stylesheets
├── routes/
│   ├── web.php                          # Dashboard routes + internal API
│   ├── api.php                          # Public API routes (Electron)
│   └── channels.php
├── storage/
│   ├── app/public/videos/               # Video files (served via symlink)
│   ├── logs/                            # Application logs
│   └── framework/
├── tests/                               # Test suite
├── config/                              # Configuration files
├── bootstrap/                           # Framework bootstrap
├── composer.json
├── package.json
└── README.md
```

---

## Development

### Running Tests

```bash
php artisan test
```

### Building Frontend

Development:
```bash
npm run dev
```

Production:
```bash
npm run prod
```

### Checking Code Quality

```bash
# Laravel Pint (code formatter)
./vendor/bin/pint

# PHP Stan (static analysis)
./vendor/bin/phpstan

# PHPUnit (tests)
php artisan test
```

---

## Database Migrations

### Creating Migrations

When adding new features:

```bash
# Create a new migration
php artisan make:migration create_new_table

# Run all pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback
```

### Key Tables

- `users` - User accounts with roles
- `videos` - Video metadata and storage paths
- `schedules` - Video playback schedules
- `video_reports` - Playback telemetry events
- `system_settings` - Application configuration
- `settings` - Legacy settings (deprecated)
- `logs` - Event logs
- `personal_access_tokens` - API tokens for Sanctum

---

## Security

### API Key Management

- Store API keys in `system_settings` table
- Keys are required for all `/api/*` endpoints (except `/health`)
- Support both query parameter and header-based authentication
- Keys can be rotated via dashboard without downtime

### Authentication

- Dashboard: Laravel session-based authentication
- API: Request header-based key authentication
- Password hashing: Laravel default (bcrypt)
- CSRF protection enabled for form submissions

### Best Practices

1. Generate strong passwords for admin accounts
2. Rotate API keys periodically
3. Use HTTPS in production
4. Restrict database access to trusted networks
5. Enable database backups
6. Monitor logs for suspicious activity
7. Keep Laravel and dependencies updated

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Videos not serving | Verify symlink: `php artisan storage:link` |
| FFmpeg not found | Install FFmpeg: `brew install ffmpeg` (macOS) |
| Database connection error | Check `.env` database credentials |
| CORS errors on API | Check middleware in `app/Http/Kernel.php` |
| Videos not syncing | Check video directory permissions |
| Electron client not connecting | Verify API key in settings |

### Debug Mode

Enable debug mode in `.env`:

```env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

### Logs Location

Application logs: `storage/logs/laravel.log`

View logs in real-time:
```bash
tail -f storage/logs/laravel.log
```

---

## Performance Optimization

### Caching

The application uses Laravel's cache for:
- System settings (TTL: configurable)
- Online client status (TTL: 24 hours)
- Video metadata (TTL: 1 hour)

Clear cache when needed:
```bash
php artisan cache:clear
php artisan route:cache
php artisan config:cache
```

### Database Optimization

- Add indexes on frequently queried columns
- Archive old log entries periodically
- Optimize video_reports table: `php artisan db:optimize`

### Video Serving

- Use CDN for video content in production
- Enable gzip compression for APIs
- Configure HTTP caching headers
- Use HLS/DASH for adaptive bitrate streaming (future enhancement)

---

## Deployment

### Production Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Set `APP_ENV=production`
- [ ] Generate app key: `php artisan key:generate`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Create symlink: `php artisan storage:link`
- [ ] Set proper file permissions
- [ ] Enable HTTPS
- [ ] Configure backups
- [ ] Set up monitoring/logging
- [ ] Create super admin: `php artisan make:superadmin admin@example.com`

### Server Requirements

- **Web Server**: Nginx or Apache with PHP-FPM
- **Database**: MySQL 5.7+ or PostgreSQL
- **PHP Extensions**: JSON, OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype
- **Disk Space**: Minimum 50GB for video storage (configurable)

---

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests: `php artisan test`
5. Submit a pull request

---

## License

MIT License - see LICENSE file for details

---

## Support

For issues and feature requests:

1. Check [API Specification](docs/API_SPECIFICATION.md)
2. Review [Electron Client Guide](docs/ELECTRON_CLIENT_API.md)
3. Check logs: `storage/logs/laravel.log`
4. Open an issue on GitHub

---

**Last Updated**: March 14, 2026  
**Version**: 2.0.0 (with Super Admin support)
