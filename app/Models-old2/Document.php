<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Document;
use OfficeDocument;

class Document extends Model
{
    use HasFactory , SoftDeletes;

    protected $table = 'documents';
    protected $fillable = [
        'name', 'status','is_required'
    ];

    protected $casts = [
        'status'     => 'integer',
        'is_required'    => 'integer',
    ];

    public function officeDocument(){
        return $this->hasMany(OfficeDocument::class, 'documentId','id');
    }
    public function scopeList($query)
    {
        return $query->orderByRaw('deleted_at IS NULL DESC, deleted_at DESC')->orderBy('updated_at', 'desc');
    }


}

