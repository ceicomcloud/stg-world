<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOrder extends Model
{
    use HasFactory;

    protected $table = 'user_orders';

    protected $fillable = [
        'user_id',
        'package_key',
        'gold_amount',
        'amount_eur',
        'status',
        'provider',
        'provider_order_id',
    ];

    protected $casts = [
        'gold_amount' => 'integer',
        'amount_eur' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}