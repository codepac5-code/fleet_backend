<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeKycDocument extends Model
{
    use SoftDeletes;
    use ResolvesTenantConnection;

    protected $table = 'office_kyc_documents';

    protected $fillable = [
        'officeId',
        'name',
        'file',
        'status',
        'note',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'date',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class, 'officeId');
    }
}
