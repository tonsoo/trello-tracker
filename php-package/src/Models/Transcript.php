<?php

namespace Tonso\TrelloTracker\Models;

use Illuminate\Database\Eloquent\Model;

class Transcript extends Model
{
    protected $table = 'transcripts';

    protected $fillable = [
        'meeting_id',
        'body',
        'ended_at'
    ];

    protected $casts = [
        'body' => 'array',
        'ended_at' => 'datetime',
    ];
}