<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Help content is per country — each shard carries its own wording.
 */
class HelpSuggestion extends Model
{
    use ResolvesTenantConnection;

    use HasFactory;

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'isActive',
        'priority',
        'category',
        'read_minutes',
        'created_by',
        'target_user',
    ];


    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
