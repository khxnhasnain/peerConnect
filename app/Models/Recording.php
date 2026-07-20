<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'file_path',
        'file_name',
    ];

    /**
     * Get the duration of the recording.
     *
     * @return string
     */
    public function getDurationAttribute()
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("recording_duration_{$this->id}", function () {
            try {
                if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($this->file_path)) {
                    return '00:00';
                }
                $path = \Illuminate\Support\Facades\Storage::disk('local')->path($this->file_path);
                $escapedPath = escapeshellarg($path);
                
                // Use ffmpeg with null format to get total duration
                $command = "ffmpeg -i " . $escapedPath . " -f null - 2>&1";
                $output = shell_exec($command);
                
                if (preg_match_all('/time=(\d{2}:\d{2}:\d{2}\.\d{2})/', $output, $matches)) {
                    $lastTime = end($matches[1]); // e.g. "00:01:23.45"
                    $parts = explode(':', $lastTime);
                    if (count($parts) === 3) {
                        $hours = intval($parts[0]);
                        $minutes = intval($parts[1]);
                        $secondsWithMs = $parts[2];
                        $seconds = intval(round(floatval($secondsWithMs)));
                        
                        // Handle carryover
                        if ($seconds >= 60) {
                            $seconds = 0;
                            $minutes++;
                            if ($minutes >= 60) {
                                $minutes = 0;
                                $hours++;
                            }
                        }

                        if ($hours > 0) {
                            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        } else {
                            return sprintf('%02d:%02d', $minutes, $seconds);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Return fallback on error
            }
            return '00:00';
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class)->withTrashed();
    }

    public function participants()
    {
        return $this->hasMany(RecordingParticipant::class);
    }

    public function downloadRequests()
    {
        return $this->hasMany(RecordingDownloadRequest::class);
    }
}
