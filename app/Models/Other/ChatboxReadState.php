<?php

namespace App\Models\Other;

use Illuminate\Database\Eloquent\Model;

class ChatboxReadState extends Model
{
    protected $table = 'chatbox_read_states';

    protected $fillable = [
        'user_id',
        'channel',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}