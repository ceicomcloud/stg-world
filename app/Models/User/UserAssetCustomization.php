<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAssetCustomization extends Model
{
    use HasFactory;

    protected $table = 'user_asset_customizations';

    protected $fillable = [
        'user_id',
        'template_build_id',
        'display_name',
        'icon_path',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Template\TemplateBuild::class, 'template_build_id');
    }
}