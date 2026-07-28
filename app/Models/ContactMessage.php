<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = ['intent', 'name', 'email', 'phone', 'company', 'message', 'status'];
}
