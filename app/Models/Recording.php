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
