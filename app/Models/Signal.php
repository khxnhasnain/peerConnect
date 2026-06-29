<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'from_peer_id',
        'to_peer_id',
        'signal_data',
        'processed',
    ];

    protected $casts = [
        'processed' => 'boolean',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
