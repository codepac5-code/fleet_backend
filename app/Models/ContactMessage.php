<?php

namespace App\Models;

use App\Traits\ResolvesTenantConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Messages raised inside a country's own panel.
 */
class ContactMessage extends Model
{
    use ResolvesTenantConnection;

    protected $table = 'contact_messages';

    protected $fillable = ['intent', 'name', 'email', 'phone', 'company', 'message', 'status'];
}
