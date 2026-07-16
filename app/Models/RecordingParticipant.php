<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordingParticipant extends Model
{
    protected $fillable = [
        'recording_id',
        'user_id',
    ];

    public function recording()
    {
        return $this->belongsTo(Recording::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
