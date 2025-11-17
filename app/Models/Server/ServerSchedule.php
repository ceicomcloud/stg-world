<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerSchedule extends Model
{
    use HasFactory;

    protected $table = 'server_schedules';

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'payload' => 'array',
        'enabled' => 'boolean',
    ];

    protected $fillable = [
        'type', // 'truce' | 'bonus'
        'starts_at',
        'ends_at',
        'payload', // JSON: selon type
        'message',
        'enabled',
    ];
}