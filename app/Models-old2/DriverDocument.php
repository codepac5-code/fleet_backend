<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Document;
use Driver;
use DriverDocument;

class DriverDocument extends Model
{
    use SoftDeletes;
    use ResolvesTenantConnection;

    protected $table = 'driver_documents';

    protected $fillable = [
        'driverId',
        'document_id',
        'name',
        'file',
        'status',
        'note',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driverId');
    }

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
