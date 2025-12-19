<?php

namespace Tonso\TrelloTracker\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'external_id',
        'text',
        'source',
        'processed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'processed' => 'boolean',
    ];
}
