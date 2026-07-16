<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeetingParticipant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'meeting_id',
        'user_id',
        'peer_id',
        'is_audio_muted',
        'is_video_off',
        'hand_raised',
        'is_admin',
    ];

    protected $casts = [
        'is_audio_muted' => 'boolean',
        'is_video_off' => 'boolean',
        'hand_raised' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
