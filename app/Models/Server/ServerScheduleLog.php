<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerScheduleLog extends Model
{
    use HasFactory;

    protected $table = 'server_schedule_logs';

    protected $casts = [
        'applied_at' => 'datetime',
        'changes' => 'array',
    ];

    protected $fillable = [
        'schedule_id',
        'type', // 'apply'
        'applied_at',
        'message',
        'changes', // JSON
    ];
}