<?php

namespace App\Models\User;

use App\Models\Template\TemplateInventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInventory extends Model
{
    use HasFactory;

    protected $table = 'user_inventories';

    protected $fillable = [
        'user_id',
        'template_inventory_id',
        'quantity',
        'acquired_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'acquired_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateInventory::class, 'template_inventory_id');
    }
}