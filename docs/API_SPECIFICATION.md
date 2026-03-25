# API Specification - Video Player Laravel Dashboard

## Overview

This document describes all available API endpoints for the Video Player Dashboard system. The API is divided into two sections:

1. **Internal Dashboard API** - For authenticated web dashboard (Vue.js frontend)
2. **External Client API** - For Electron desktop applications (with API key authentication)

---

## Authentication

### Dashboard API
- **Type**: Session-based authentication (Laravel web authentication)
- **Requirements**: User must be logged in via web dashboard
- **Verification**: Cookie-based session token

### Client API
- **Type**: API Key-based authentication
- **Requirements**: Valid API key must be provided in request headers
- **Header**: `X-API-Key: your-api-key-here`
- **Alternative**: Can pass `api_key` as query parameter

---

## Base URLs

- **Dashboard API**: `http://localhost:8000/api` (requires authentication)
- **Client API**: `http://player-server:8000/api` (requires API key)

---

## Dashboard API Endpoints (Internal)

All dashboard endpoints require authentication and are available under `/api/*` in `routes/web.php`.

### Authentication

#### Get Current User
```http
GET /api/current-user
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@example.com",
  "user_type": "super_admin"
}
```

---

### Dashboard

#### Get Dashboard Data
```http
GET /api/dashboard/data
Authorization: Bearer {session-token}
```

**Query Parameters**: None

**Response** (200 OK):
```json
{
  "stats": {
    "total_videos": 42,
    "cached_videos": 38,
    "active_schedules": 15,
    "online_clients": 8
  },
  "recent_logs": [...],
  "upcoming_schedules": [...],
  "playback_stats": {
    "total_views": 1250,
    "total_completed": 980,
    "completion_rate": 78.4
  }
}
```

---

### Videos Management

#### List Videos
```http
GET /api/videos
Authorization: Bearer {session-token}
```

**Query Parameters**:
- `search` (optional): Search by title or filename

**Response** (200 OK):
```json
{
  "videos": [
    {
      "id": 1,
      "api_id": "local_abc123def456",
      "title": "Sample Video",
      "name": "sample-video.mp4",
      "description": "A sample video...",
      "duration": "5:30",
      "size": "125.5 MB",
      "status": "cached",
      "cached": true,
      "lastSync": "14/03/2026 10:30",
      "url": "http://localhost:8000/storage/videos/sample-video.mp4",
      "is_active": true
    }
  ],
  "stats": {
    "total": 42,
    "cached": 38,
    "available": 4,
    "total_size": "5.2 GB"
  }
}
```

#### Upload Video
```http
POST /api/videos/upload
Authorization: Bearer {session-token}
Content-Type: multipart/form-data
```

**Parameters**:
- `video` (file, required): Video file (MP4, AVI, MOV, WMV, MKV, WebM)
- `title` (string, required): Video title
- `description` (string, optional): Video description
- `duration_seconds` (integer, optional): Video duration if auto-detection fails

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Vídeo enviado com sucesso",
  "video": {
    "id": 1,
    "title": "New Video",
    "name": "abc_123-video.mp4",
    "duration": "10:25",
    "size": 245123456,
    "status": "cached",
    "cached": true,
    "url": "http://localhost:8000/storage/videos/abc_123-video.mp4"
  }
}
```

**Error** (400 Bad Request):
```json
{
  "success": false,
  "message": "Validação falhou",
  "errors": {
    "video": ["O ficheiro deve ser um vídeo válido"]
  }
}
```

#### Sync Local Videos
```http
POST /api/videos/sync
Authorization: Bearer {session-token}
```

**Parameters**: None

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Sincronização concluída. 42 vídeos sincronizados (5 novos, 2 removidos).",
  "updated": 42,
  "created": 5,
  "deleted": 2
}
```

#### Preview Video Metadata
```http
POST /api/videos/preview
Authorization: Bearer {session-token}
Content-Type: multipart/form-data
```

**Parameters**:
- `file` (file, required): Video file for metadata detection

**Response** (200 OK):
```json
{
  "success": true,
  "duration": "5:30",
  "size": 125500000,
  "codec": "h264",
  "format": "mp4"
}
```

#### Download Video (Cache)
```http
POST /api/videos/{id}/download
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Vídeo baixado com sucesso",
  "video": {
    "id": 1,
    "status": "cached",
    "cached": true,
    "file_path": "videos/video_abc123_xyz.mp4"
  }
}
```

#### Update Video
```http
PUT /api/videos/{id}
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**:
```json
{
  "title": "Updated Title",
  "description": "New description",
  "is_active": true
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Vídeo atualizado com sucesso",
  "video": { ... }
}
```

#### Remove from Cache
```http
DELETE /api/videos/{id}/cache
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Vídeo removido do cache",
  "video": { ... }
}
```

#### Delete Video
```http
DELETE /api/videos/{id}
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Vídeo deletado com sucesso"
}
```

---

### Schedules Management

#### List Schedules
```http
GET /api/schedules
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "schedules": [
    {
      "id": 1,
      "video_url": "http://localhost:8000/storage/videos/sample.mp4",
      "title": "Morning Slideshow",
      "days": ["monday", "tuesday", "wednesday"],
      "time": "09:00",
      "monitor": "principal",
      "active": true,
      "created_at": "2026-03-14 10:00:00"
    }
  ]
}
```

#### Create Schedule
```http
POST /api/schedules
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**:
```json
{
  "video_url": "http://localhost:8000/storage/videos/sample.mp4",
  "title": "Evening Display",
  "days": ["monday", "friday"],
  "time": "18:00",
  "monitor": "principal",
  "active": true,
  "subtitle_url": "http://localhost:8000/storage/subtitles/sample.srt",
  "window_config": {
    "position": {
      "anchor": "bottom-right",
      "x": null,
      "y": null,
      "margin": 50
    },
    "size": {
      "width": 854,
      "height": 480
    }
  }
}
```

**Validation Rules**:
- `title` (required, string, max:255): Schedule title
- `video_url` (required, string, max:500): Video file URL
- `days` (required, array, min:1): Scheduled days
- `time` (required, date_format:H:i or H:i:s): Schedule time
- `monitor` (required, string, max:255): Monitor/zone identifier
- `active` (boolean): Whether schedule is enabled (default: true)
- `subtitle_url` (nullable, string, max:500, url): URL to subtitle file
- `window_config` (nullable, array): Window positioning configuration
  - `position` (required in window_config, object):
    - `anchor` (string, in: top-left, top-right, bottom-left, bottom-right, center, top-center, bottom-center)
    - `x` (nullable, integer, between: -3840,3840): X-axis offset
    - `y` (nullable, integer, between: -2160,2160): Y-axis offset
    - `margin` (integer, between: 0,300): Margin from edge
  - `size` (required in window_config, object):
    - `width` (integer, between: 320,3840): Window width in pixels
    - `height` (integer, between: 180,2160): Window height in pixels

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Agendamento criado com sucesso",
  "schedule": {
    "id": 5,
    "title": "Evening Display",
    "days": ["monday", "friday"],
    "time": "18:00",
    "monitor": "principal",
    "active": true,
    "subtitle_url": "http://localhost:8000/storage/subtitles/sample.srt",
    "window_config": {
      "position": {
        "anchor": "bottom-right",
        "x": null,
        "y": null,
        "margin": 50
      },
      "size": {
        "width": 854,
        "height": 480
      }
    },
    "created_at": "2026-03-14T14:30:00Z",
    "updated_at": "2026-03-14T14:30:00Z"
  }
}
```

#### Update Schedule
```http
PUT /api/schedules/{id}
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**: Same as Create Schedule, with same validation rules.

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Agendamento atualizado",
  "schedule": { ... }
}
```

#### Toggle Schedule Status
```http
POST /api/schedules/{id}/toggle
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "active": false
}
```

#### Duplicate Schedule
```http
POST /api/schedules/{id}/duplicate
Authorization: Bearer {session-token}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Agendamento duplicado com sucesso",
  "schedule": { ... }
}
```

#### Get Today's Schedules
```http
GET /api/schedules/today
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "schedules": [
    {
      "id": 1,
      "video_url": "...",
      "title": "...",
      "time": "09:00"
    }
  ]
}
```

#### Delete Schedule
```http
DELETE /api/schedules/{id}
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Agendamento deletado"
}
```

---

### Users Management (Admin Only)

#### List Users
```http
GET /api/users
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "users": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "user_type": "super_admin",
      "is_superadmin": true,
      "created_at": "2026-03-14 10:00:00"
    }
  ]
}
```

#### Create User
```http
POST /api/users
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**:
```json
{
  "name": "New User",
  "email": "user@example.com",
  "password": "SecurePassword123",
  "user_type": "manager"
}
```

#### Update User
```http
PUT /api/users/{id}
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**:
```json
{
  "name": "Updated Name",
  "user_type": "admin"
}
```

#### Delete User
```http
DELETE /api/users/{id}
Authorization: Bearer {session-token}
```

---

### System Settings

#### Get Settings
```http
GET /api/system-settings
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "settings": {
    "api_endpoint": "http://localhost:8000/api/videos",
    "api_key": "SECRET_KEY_HERE",
    "sync_interval": 30,
    "default_monitor": "principal",
    "enable_notifications": true,
    ...
  }
}
```

#### Update Settings
```http
POST /api/system-settings
Authorization: Bearer {session-token}
Content-Type: application/json
```

**Body**:
```json
{
  "api_endpoint": "http://new-endpoint/api",
  "api_key": "new_api_key",
  "sync_interval": 60,
  "enable_notifications": false
}
```

#### Test Connection
```http
POST /api/system-settings/test-connection
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Conexão bem-sucedida"
}
```

#### Get Settings History
```http
GET /api/system-settings/history
Authorization: Bearer {session-token}
```

#### Export Settings
```http
GET /api/system-settings/export
Authorization: Bearer {session-token}
```

---

### Client Monitoring

#### Get Online Clients
```http
GET /api/client/online
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "clients": [
    {
      "client_id": "CLIENT_001",
      "device_name": "Display Monitor 01",
      "last_heartbeat": "2026-03-14 14:55:30",
      "status": "online"
    }
  ],
  "total_online": 8
}
```

#### Get Client Stats
```http
GET /api/client/stats
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "total_clients": 10,
  "online_clients": 8,
  "offline_clients": 2,
  "last_update": "2026-03-14 14:56:00"
}
```

#### Admin Client Dashboard
```http
GET /api/admin/clients
Authorization: Bearer {session-token}
```

Returns HTML view of all connected clients.

---

### Video Reports

#### Get Report Statistics
```http
GET /api/videos/report/stats
Authorization: Bearer {session-token}
```

**Response** (200 OK):
```json
{
  "total_views": 1250,
  "total_completed": 980,
  "completion_rate": 78.4,
  "unique_sessions": 150,
  "last_24h": {
    "views": 45,
    "completions": 38
  }
}
```

#### Get Video Reports
```http
GET /api/videos/{videoId}/reports
Authorization: Bearer {session-token}
```

**Query Parameters**:
- `days` (optional, default=7): Number of days to include

**Response** (200 OK):
```json
{
  "video_id": 1,
  "reports": [
    {
      "id": 1,
      "event": "playback_started",
      "device_info": "Windows 10 - Electron App",
      "session_id": "sess_abc123",
      "viewed_at": "2026-03-14 10:30:00"
    }
  ]
}
```

---

### Maintenance

#### Clear All Caches
```http
GET /ops/clear-all
Authorization: Bearer {session-token}
```

**Requirements**: Admin or Super Admin user

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Caches limpos com sucesso."
}
```

---

## Client API Endpoints (External)

All client endpoints require API key authentication and are available under `/api/*` in `routes/api.php`.

### Health Check

#### Service Health
```http
GET /api/health
```

**Response** (200 OK):
```json
{
  "status": "OK",
  "service": "Video API"
}
```

No authentication required.

---

### Schedule Retrieval

#### Get Schedules for Client
```http
GET /api/schedules/clients
X-API-Key: your-api-key-here
X-Client-ID: ELECTRON_VIDEO_PLAYER
```

**Response** (200 OK):
```json
{
  "schedules": [
    {
      "id": 1,
      "video_url": "http://api-server/storage/videos/sample.mp4",
      "title": "Morning Display",
      "days": ["monday", "tuesday", "wednesday"],
      "time": "09:00",
      "monitor": "principal"
    }
  ]
}
```

#### Get Scheduled Videos for Today
```http
GET /api/scheduled/videos
X-API-Key: your-api-key-here
X-Client-ID: ELECTRON_VIDEO_PLAYER
```

**Response** (200 OK):
```json
{
  "today": "2026-03-14",
  "videos": [
    {
      "id": 1,
      "title": "Morning Show",
      "video_id": 11,
      "video_url": "http://api-server/storage/videos/video1.mp4",
      "video_size": 12500000,
      "subtitle_url": "http://api-server/storage/subtitles/morning.srt",
      "time": "09:00",
      "days": ["monday", "tuesday", "wednesday"],
      "monitor": "principal",
      "active": true,
      "duration": "30:00",
      "window_config": {
        "position": {
          "anchor": "bottom-right",
          "x": null,
          "y": null,
          "margin": 50
        },
        "size": {
          "width": 854,
          "height": 480
        }
      },
      "created_at": "2026-03-10T14:30:00Z",
      "updated_at": "2026-03-14T14:30:00Z"
    }
  ]
}
```

**Response Fields**:
- `id` (integer): Schedule identifier
- `title` (string): Schedule display title
- `video_id` (integer): Associated video ID
- `video_url` (string): Full URL to video file
- `video_size` (integer): Video file size in bytes (0 if unavailable)
- `subtitle_url` (string|null): URL to SRT subtitle file or null
- `time` (string): Scheduled time (HH:MM:SS format)
- `days` (array): Scheduled days (e.g., ["monday", "tuesday"])
- `monitor` (string): Target monitor/zone
- `active` (boolean): Whether schedule is enabled
- `duration` (string): Video duration (MM:SS format)
- `window_config` (object): Window positioning and sizing configuration
  - `position` (object):
    - `anchor` (string): Window position anchor (top-left, top-right, bottom-left, bottom-right, center, top-center, bottom-center)
    - `x` (integer|null): X-axis offset in pixels
    - `y` (integer|null): Y-axis offset in pixels
    - `margin` (integer): Margin from screen edge
  - `size` (object):
    - `width` (integer): Window width in pixels
    - `height` (integer): Window height in pixels
- `created_at` (string): Creation timestamp (ISO 8601)
- `updated_at` (string): Last update timestamp (ISO 8601)

#### Get Subtitle File
```http
GET /api/subtitles/{schedule_id}
X-API-Key: your-api-key-here
X-Client-ID: ELECTRON_VIDEO_PLAYER
```

**Response** (200 OK):
```
00:00:00,000 --> 00:00:05,000
Welcome to the video

00:00:05,100 --> 00:00:10,000
This is a subtitle example
```

**Response Headers**:
- `Content-Type: text/plain; charset=utf-8`
- `Content-Disposition: attachment; filename="subtitles.srt"`

**Error Responses**:

404 Not Found (if schedule not found):
```json
{
  "success": false,
  "message": "Schedule not found",
  "error": "schedule_not_found"
}
```

404 Not Found (if subtitle file not found):
```json
{
  "success": false,
  "message": "Subtitle file not found for this schedule",
  "error": "subtitle_not_found"
}
```

**Subtitle Format Support**:
- Remote URLs (HTTP/HTTPS) - Proxied through API
- Local storage files - From `storage/app/public/` directory
- Fallback support - Returns 404 if no subtitle available

---

### Telemetry & Reporting

#### Submit Playback Report
```http
POST /api/videos/report
X-API-Key: your-api-key-here
X-Client-ID: ELECTRON_VIDEO_PLAYER
Content-Type: application/json
```

**Body**:
```json
{
  "video_id": 1,
  "event": "playback_started",
  "session_id": "sess_abc123xyz",
  "device_info": "Windows 10 - Display-01",
  "duration_watched": 300,
  "timestamp": "2026-03-14T14:30:00Z"
}
```

**Allowed Events**:
- `playback_started`: Video playback initiated
- `playback_paused`: Video playback paused
- `playback_resumed`: Video playback resumed
- `completed`: Video playback completed
- `error`: Playback error occurred

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Relatório registrado com sucesso",
  "report_id": "rpt_abc123"
}
```

#### Client Heartbeat (Ping)
```http
POST /api/ping
X-API-Key: your-api-key-here
X-Client-ID: ELECTRON_VIDEO_PLAYER
Content-Type: application/json
```

**Body**:
```json
{
  "client_id": "CLIENT_001",
  "device_name": "Display Monitor 01",
  "status": "online"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Heartbeat recebido"
}
```

This endpoint is called periodically by the Electron client to maintain online status. The server maintains a 24-hour cache of online clients.

---

## Error Responses

All endpoints return errors in this format:

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Authentication required",
  "error": "Missing API key"
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

### 422 Unprocessable Entity
```json
{
  "success": false,
  "message": "Validação falhou",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Erro ao processar requisição: [error details]"
}
```

---

## Rate Limiting

- Dashboard API: No rate limiting
- Client API: Standard rate limiting applies
- `/ops/clear-all`: 10 requests per 1 minute

---

## Data Models

### Video
```json
{
  "id": 1,
  "api_id": "local_abc123def456",
  "title": "Video Title",
  "name": "video-file.mp4",
  "description": "Video description",
  "duration": "5:30",
  "size": 125500000,
  "url": "http://localhost:8000/storage/videos/video-file.mp4",
  "thumbnail_url": null,
  "status": "cached",
  "cached": true,
  "file_path": "videos/video-file.mp4",
  "is_active": true,
  "last_sync": "2026-03-14 10:30:00",
  "created_at": "2026-03-14 10:00:00",
  "updated_at": "2026-03-14 10:30:00"
}
```

### Schedule
```json
{
  "id": 1,
  "video_url": "http://localhost:8000/storage/videos/video.mp4",
  "title": "Schedule Title",
  "days": ["monday", "tuesday", "wednesday"],
  "time": "09:00",
  "monitor": "principal",
  "active": true,
  "created_at": "2026-03-14 10:00:00",
  "updated_at": "2026-03-14 10:00:00"
}
```

### User
```json
{
  "id": 1,
  "name": "User Name",
  "email": "user@example.com",
  "user_type": "super_admin",
  "is_superadmin": true,
  "is_admin": false,
  "is_manager": false,
  "email_verified_at": "2026-03-14 10:00:00",
  "created_at": "2026-03-14 10:00:00",
  "updated_at": "2026-03-14 10:00:00"
}
```

---

## Changelog

### Version 1.0.0 (March 14, 2026)
- Initial API specification
- Unified documentation for dashboard and client APIs
- Added super admin authentication support
- Standardized error response format
- Documented all endpoint requirements and responses
