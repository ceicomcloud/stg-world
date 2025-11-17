<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotTickRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'started_at',
        'finished_at',
        'planets_processed',
        'resources_generated_json',
        'resources_spent_json',
        'details_path',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}