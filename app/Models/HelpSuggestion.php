<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'isActive',
        'priority',
        'category',
        'created_by',
        'target_user',
    ];


    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}
