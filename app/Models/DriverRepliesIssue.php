<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRepliesIssue extends Model
{
    use HasFactory;

    protected $table = 'replies';

   protected $fillable = [
       'issueId',
       'sender_type',
       'sender_id',
       'senderName',
       'content',
       'imageUrl',
   ];

   protected $dates = [
       'created_at',
       'updated_at',
   ];


   public function issue()
   {
       return $this->belongsTo(DriverRepliesIssue::class, 'issueId');
   }

  
   public function sender()
   {
       return $this->morphTo();
   }
}
