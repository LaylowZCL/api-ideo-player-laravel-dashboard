<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class VideoReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_id',
        'video_title',
        'event_type',
        'event_data',
        'playback_position',
        'playback_duration',
        'user_agent',
        'platform',
        'app_version',
        'ip_address',
        'session_id',
        'trigger_type',
        'completed',
        'viewed_at'
    ];

    protected $casts = [
        'event_data' => 'array',
        'viewed_at' => 'datetime',
        'completed' => 'boolean',
        'playback_position' => 'float',
        'playback_duration' => 'float'
    ];

    // Relacionamento com o vídeo (se tiver tabela de vídeos)
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // Scopes úteis
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('viewed_at', today());
    }

    public function scopeForVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId);
    }

    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    // Accessor para dados do evento formatados
    protected function eventDataFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->event_data ? json_encode($this->event_data, JSON_PRETTY_PRINT) : null
        );
    }

    // Método para marcar como completo
    public function markAsCompleted($duration = null)
    {
        $this->completed = true;
        $this->event_type = 'video_completed';
        if ($duration) {
            $this->playback_duration = $duration;
        }
        $this->save();
    }

    // Método para obter estatísticas do vídeo
    public static function getVideoStats($videoId = null)
    {
        $query = self::query();
        
        if ($videoId) {
            $query->where('video_id', $videoId);
        }

        return [
            'total_views' => $query->where('event_type', 'playback_started')->count(),
            'total_completions' => $query->where('event_type', 'video_completed')->count(),
            'avg_view_duration' => $query->whereNotNull('playback_duration')->avg('playback_duration'),
            'today_views' => $query->where('event_type', 'playback_started')->today()->count(),
            'unique_sessions' => $query->distinct('session_id')->count('session_id'),
            'by_platform' => $query->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->get()
                ->pluck('count', 'platform')
        ];
    }
}
