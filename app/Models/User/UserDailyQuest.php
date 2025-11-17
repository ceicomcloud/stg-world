<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDailyQuest extends Model
{
    use HasFactory;

    protected $table = 'user_daily_quests';

    protected $fillable = [
        'user_id',
        'date',
        'quests',
    ];

    protected $casts = [
        'quests' => 'array',
    ];
}