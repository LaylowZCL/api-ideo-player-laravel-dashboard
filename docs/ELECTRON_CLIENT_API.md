# Electron Client API Guide

## Overview

This guide provides detailed instructions for developing and integrating the Electron desktop application with the Video Player API Server.

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Authentication](#authentication)
3. [Core API Methods](#core-api-methods)
4. [Scheduling & Playback](#scheduling--playback)
5. [Telemetry & Reporting](#telemetry--reporting)
6. [Heartbeat & Status](#heartbeat--status)
7. [Error Handling](#error-handling)
8. [Implementation Examples](#implementation-examples)

---

## Getting Started

### Prerequisites

- Node.js >= 14
- Electron >= 13
- axios or similar HTTP client

### Installation

```bash
npm install axios
```

### Configuration

Store these values in your Electron app's configuration:

```javascript
const API_CONFIG = {
  BASE_URL: 'http://server-ip:8000/api',
  API_KEY: 'VIDEO_POPUP_SECRET_2025',  // From SystemSetting
  CLIENT_ID: 'ELECTRON_VIDEO_PLAYER',
  SYNC_INTERVAL: 30000, // milliseconds
  HEARTBEAT_INTERVAL: 60000 // milliseconds
};
```

Store these in a secure location (environment variables, secure storage, or encrypted config file).

---

## Authentication

### Headers

Every request (except `/health`) must include authentication headers:

```javascript
const headers = {
  'X-API-Key': API_CONFIG.API_KEY,
  'X-Client-ID': API_CONFIG.CLIENT_ID,
  'Content-Type': 'application/json'
};
```

### Example Authenticated Request

```javascript
const axios = require('axios');

const client = axios.create({
  baseURL: API_CONFIG.BASE_URL,
  headers: {
    'X-API-Key': API_CONFIG.API_KEY,
    'X-Client-ID': API_CONFIG.CLIENT_ID
  }
});

// All requests through this client are automatically authenticated
```

### API Key Rotation

- API keys are stored in `system_settings` table
- Server admin can rotate keys via dashboard
- Update `API_CONFIG.API_KEY` when new keys are distributed
- Support multiple valid keys during transition period

---

## Core API Methods

### 1. Health Check

Check service availability before making requests:

```javascript
async function checkServerHealth() {
  try {
    const response = await axios.get(`${API_CONFIG.BASE_URL}/health`);
    console.log('Server status:', response.data.status);
    return response.data.status === 'OK';
  } catch (error) {
    console.error('Server unreachable:', error.message);
    return false;
  }
}
```

**Usage**: Call at app startup and periodically to detect server unavailability.

---

### 2. Get Schedules

Retrieve all available schedules from the server:

```javascript
async function fetchSchedules() {
  try {
    const response = await client.get('/schedules/clients');
    const schedules = response.data.schedules;
    
    console.log(`Fetched ${schedules.length} schedules`);
    
    return schedules.map(schedule => ({
      id: schedule.id,
      videoUrl: schedule.video_url,
      title: schedule.title,
      days: schedule.days,  // ['monday', 'tuesday', ...]
      time: schedule.time,  // '09:00'
      monitor: schedule.monitor  // 'principal', 'secundario', 'todos'
    }));
  } catch (error) {
    console.error('Failed to fetch schedules:', error.response?.data);
    throw error;
  }
}
```

**Response**: Array of schedule objects with video playback specifications.

---

### 3. Get Today's Videos

Get only videos scheduled for today:

```javascript
async function getTodayVideos() {
  try {
    const response = await client.get('/scheduled/videos');
    
    return response.data.videos.map(video => ({
      id: video.id,
      url: video.url,
      title: video.title,
      time: video.time,  // Scheduled play time
      duration: video.duration  // Video length
    }));
  } catch (error) {
    console.error('Failed to fetch today\'s videos:', error.response?.data);
    throw error;
  }
}
```

**Usage**: Call daily to update the playback queue.

---

## Scheduling & Playback

### Playback Management Service

Implement a service to manage video playback based on schedules:

```javascript
class PlaybackManager {
  constructor(schedules = []) {
    this.schedules = schedules;
    this.currentVideo = null;
    this.playQueue = [];
  }

  /**
   * Process schedules and build today's playback queue
   */
  buildPlayQueue(today = new Date()) {
    const dayName = this.getDayName(today);
    
    this.playQueue = this.schedules
      .filter(schedule => {
        // Check if schedule applies to today
        return schedule.days.includes(dayName) && schedule.active;
      })
      .sort((a, b) => {
        // Sort by time (earliest first)
        return a.time.localeCompare(b.time);
      })
      .map(schedule => ({
        ...schedule,
        playTime: this.timeToMinutes(schedule.time)
      }));
    
    console.log(`Built queue with ${this.playQueue.length} videos for today`);
    return this.playQueue;
  }

  /**
   * Get next video to play
   */
  getNextVideo(currentTime = new Date()) {
    const minutes = currentTime.getHours() * 60 + currentTime.getMinutes();
    
    const nextVideo = this.playQueue.find(video => 
      video.playTime > minutes
    );
    
    return nextVideo || null;
  }

  /**
   * Convert time string to minutes (e.g., "09:30" -> 570)
   */
  timeToMinutes(timeStr) {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return hours * 60 + minutes;
  }

  /**
   * Get day name from date
   */
  getDayName(date) {
    const days = ['sunday', 'monday', 'tuesday', 'wednesday', 
                  'thursday', 'friday', 'saturday'];
    return days[date.getDay()];
  }
}

// Usage
const schedules = await fetchSchedules();
const playbackManager = new PlaybackManager(schedules);
playbackManager.buildPlayQueue();

const nextVideo = playbackManager.getNextVideo();
if (nextVideo) {
  console.log(`Next video: ${nextVideo.title} at ${nextVideo.time}`);
}
```

---

## Telemetry & Reporting

### Playback Events

Report playback events to track viewing statistics:

```javascript
class TelemetryService {
  constructor(clientId) {
    this.clientId = clientId;
    this.sessionId = this.generateSessionId();
  }

  /**
   * Generate unique session ID
   */
  generateSessionId() {
    return `sess_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  /**
   * Report playback started
   */
  async reportPlaybackStarted(videoId) {
    return this.reportEvent(videoId, 'playback_started');
  }

  /**
   * Report playback paused
   */
  async reportPlaybackPaused(videoId) {
    return this.reportEvent(videoId, 'playback_paused');
  }

  /**
   * Report playback resumed
   */
  async reportPlaybackResumed(videoId) {
    return this.reportEvent(videoId, 'playback_resumed');
  }

  /**
   * Report video completed
   */
  async reportPlaybackCompleted(videoId, durationWatched = 0) {
    return this.reportEvent(videoId, 'completed', durationWatched);
  }

  /**
   * Report playback error
   */
  async reportPlaybackError(videoId, error) {
    return this.reportEvent(videoId, 'error', 0, error.message);
  }

  /**
   * Generic event reporting
   */
  async reportEvent(videoId, event, durationWatched = 0, errorMsg = null) {
    try {
      const payload = {
        video_id: videoId,
        event: event,
        session_id: this.sessionId,
        device_info: this.getDeviceInfo(),
        duration_watched: durationWatched,
        timestamp: new Date().toISOString()
      };

      if (errorMsg) {
        payload.error_message = errorMsg;
      }

      const response = await client.post('/videos/report', payload);
      console.log(`Event reported: ${event}`, response.data);
      
      return response.data;
    } catch (error) {
      console.error(`Failed to report ${event}:`, error.response?.data);
      // Queue for retry if offline
      this.queueEventForRetry(videoId, event);
    }
  }

  /**
   * Get device information
   */
  getDeviceInfo() {
    const os = require('os');
    const platform = process.platform;
    const hostname = os.hostname();
    
    return `${platform} - ${hostname}`;
  }

  /**
   * Queue events for retry when offline
   */
  queueEventForRetry(videoId, event) {
    // Implement persistent queue (localStorage, file, database)
    // Retry when connection is restored
  }
}

// Usage
const telemetry = new TelemetryService('CLIENT_001');

// When video starts
await telemetry.reportPlaybackStarted(videoId);

// When video completes
await telemetry.reportPlaybackCompleted(videoId, videoDuration);

// On error
await telemetry.reportPlaybackError(videoId, error);
```

---

## Heartbeat & Status

### Periodic Heartbeat (Ping)

Send periodic heartbeats to maintain online status:

```javascript
class HeartbeatService {
  constructor(clientId, deviceName) {
    this.clientId = clientId;
    this.deviceName = deviceName;
    this.isOnline = true;
    this.heartbeatInterval = null;
  }

  /**
   * Start heartbeat service
   */
  startHeartbeat(intervalMs = 60000) {
    this.heartbeatInterval = setInterval(() => {
      this.sendHeartbeat();
    }, intervalMs);

    console.log(`Heartbeat service started (${intervalMs}ms interval)`);
  }

  /**
   * Stop heartbeat service
   */
  stopHeartbeat() {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
      this.heartbeatInterval = null;
      console.log('Heartbeat service stopped');
    }
  }

  /**
   * Send heartbeat to server
   */
  async sendHeartbeat() {
    try {
      const response = await client.post('/ping', {
        client_id: this.clientId,
        device_name: this.deviceName,
        status: 'online'
      });

      if (!this.isOnline) {
        console.log('✓ Reconnected to server');
        this.isOnline = true;
        this.onReconnect();
      }

      return response.data;
    } catch (error) {
      if (this.isOnline) {
        console.warn('✗ Lost connection to server');
        this.isOnline = false;
        this.onDisconnect();
      }
    }
  }

  /**
   * Called when reconnecting after offline period
   */
  onReconnect() {
    // Sync missed schedules
    // Retry failed telemetry events
    // Refresh video queue
  }

  /**
   * Called when disconnecting
   */
  onDisconnect() {
    // Pause or stop playback
    // Show offline indicator to user
    // Queue events for later retry
  }
}

// Usage
const heartbeat = new HeartbeatService('DISPLAY_001', 'Monitor Room A');
heartbeat.startHeartbeat(60000); // Every 60 seconds

// When app closes
heartbeat.stopHeartbeat();
```

---

## Error Handling

### Comprehensive Error Handling

```javascript
class ApiClient {
  static async makeRequest(method, endpoint, data = null) {
    try {
      const config = {
        method,
        url: `${API_CONFIG.BASE_URL}${endpoint}`,
        headers: {
          'X-API-Key': API_CONFIG.API_KEY,
          'X-Client-ID': API_CONFIG.CLIENT_ID,
          'Content-Type': 'application/json'
        }
      };

      if (data) {
        config.data = data;
      }

      const response = await axios(config);
      return { success: true, data: response.data };

    } catch (error) {
      return this.handleError(error);
    }
  }

  static handleError(error) {
    const response = error.response;

    // Network error
    if (!response) {
      return {
        success: false,
        error: 'NETWORK_ERROR',
        message: 'No connection to server',
        retry: true
      };
    }

    // Authentication error
    if (response.status === 401) {
      return {
        success: false,
        error: 'AUTH_ERROR',
        message: 'Invalid API key',
        retry: false
      };
    }

    // Authorization error
    if (response.status === 403) {
      return {
        success: false,
        error: 'FORBIDDEN',
        message: 'Access denied',
        retry: false
      };
    }

    // Validation error
    if (response.status === 422) {
      return {
        success: false,
        error: 'VALIDATION_ERROR',
        message: 'Invalid request data',
        details: response.data.errors,
        retry: false
      };
    }

    // Server error
    if (response.status >= 500) {
      return {
        success: false,
        error: 'SERVER_ERROR',
        message: response.data.message || 'Server error',
        retry: true
      };
    }

    // Unknown error
    return {
      success: false,
      error: 'UNKNOWN_ERROR',
      message: 'An unknown error occurred',
      retry: true
    };
  }
}

// Usage
const result = await ApiClient.makeRequest('GET', '/schedules/clients');
if (!result.success) {
  if (result.retry) {
    // Retry later
  } else {
    // Show user error
    console.error(result.message);
  }
}
```

---

## Implementation Examples

### Complete Electron Main Process

```javascript
// main.js
const { app, BrowserWindow } = require('electron');
const path = require('path');

let mainWindow;

// Services
let playbackManager;
let telemetryService;
let heartbeatService;

async function createWindow() {
  mainWindow = new BrowserWindow({
    width: 1920,
    height: 1080,
    fullscreen: true,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      nodeIntegration: false,
      contextIsolation: true
    }
  });

  mainWindow.loadFile('index.html');
}

async function initializeApp() {
  // Check server health
  const isHealthy = await checkServerHealth();
  if (!isHealthy) {
    console.error('Cannot reach server. Running in offline mode.');
    return;
  }

  // Initialize services
  const schedules = await fetchSchedules();
  playbackManager = new PlaybackManager(schedules);
  playbackManager.buildPlayQueue();

  telemetryService = new TelemetryService('DISPLAY_001');
  heartbeatService = new HeartbeatService('DISPLAY_001', 'Monitor Room A');
  
  // Start periodic tasks
  heartbeatService.startHeartbeat(60000);
  
  // Refresh schedules every hour
  setInterval(async () => {
    const schedules = await fetchSchedules();
    playbackManager.schedules = schedules;
    playbackManager.buildPlayQueue();
  }, 3600000);

  // Check for next video every minute
  setInterval(() => {
    const nextVideo = playbackManager.getNextVideo();
    if (nextVideo) {
      mainWindow.webContents.send('next-video', nextVideo);
    }
  }, 60000);
}

app.on('ready', async () => {
  createWindow();
  await initializeApp();
});

app.on('window-all-closed', () => {
  if (heartbeatService) {
    heartbeatService.stopHeartbeat();
  }
  app.quit();
});
```

### Renderer Process Integration

```javascript
// renderer.js - Handles video playback UI

const { ipcRenderer } = require('electron');
const Plyr = require('plyr');

let videoElement;
let currentVideo = null;
let telemetry;

// Initialize video player
document.addEventListener('DOMContentLoaded', () => {
  videoElement = document.getElementById('video-player');
  
  const player = new Plyr(videoElement, {
    controls: ['progress', 'play', 'volume', 'fullscreen'],
    fullscreen: { enabled: true },
    settings: ['quality', 'speed', 'loop']
  });

  telemetry = new TelemetryService('DISPLAY_001');

  // Listen for next video from main process
  ipcRenderer.on('next-video', (event, video) => {
    playVideo(video);
  });

  // Video events
  player.on('play', () => {
    if (currentVideo) {
      telemetry.reportPlaybackStarted(currentVideo.id);
    }
  });

  player.on('pause', () => {
    if (currentVideo) {
      telemetry.reportPlaybackPaused(currentVideo.id);
    }
  });

  player.on('ended', () => {
    if (currentVideo) {
      telemetry.reportPlaybackCompleted(currentVideo.id, player.duration);
    }
  });

  player.on('error', (error) => {
    if (currentVideo) {
      telemetry.reportPlaybackError(currentVideo.id, error);
    }
  });
});

function playVideo(video) {
  currentVideo = video;
  
  videoElement.src = video.url;
  videoElement.title = video.title;
  
  // Handle CORS if needed
  videoElement.crossOrigin = 'anonymous';
  
  // Report playback started
  telemetry.reportPlaybackStarted(video.id);
  
  // Start playing
  document.getElementById('player').play();
}
```

---

## Best Practices

1. **Always handle network errors gracefully**
   - Implement exponential backoff for retries
   - Queue offline events for later sync
   - Show user-friendly error messages

2. **Secure API key storage**
   - Never hardcode keys in source code
   - Use environment variables or secure storage
   - Rotate keys periodically

3. **Implement proper logging**
   - Log all API calls for debugging
   - Include timestamps and request IDs
   - Store logs locally for offline troubleshooting

4. **Test thoroughly**
   - Test with offline scenarios
   - Test network latency/slowness
   - Test video format compatibility
   - Test across different monitors/resolutions

5. **Performance optimization**
   - Cache schedules locally with TTL
   - Use low-resolution previews
   - Minimize API calls
   - Preload next video before playback

---

## Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| 401 Unauthorized | Invalid API key | Verify API key in config |
| Videos not playing | Wrong URL format | Check buildVideoPublicUrl() |
| Heartbeat failing | Server off (is) | Check server status |
| Schedules not updating | Too long cache TTL | Reduce cache timeout |
| Videos stuttering | Network bandwidth | Increase buffer size |

---

## Support

For API issues, check:
- [API Specification](../API_SPECIFICATION.md)
- Server logs: `storage/logs/laravel.log`
- Dashboard: System Settings > API Logs

---

**Last Updated**: March 14, 2026  
**API Version**: 1.0.0
