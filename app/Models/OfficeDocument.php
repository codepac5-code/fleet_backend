<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OfficeDocument extends Model
{
    use HasFactory,InteractsWithMedia,SoftDeletes;

    protected $table = 'office_documents';
    protected $fillable = [
       'officeId','documentId','isVerified'
    ];

    protected $casts = [
        'officeId'     => 'integer',
        'documentId'   => 'integer',
        'isVerified'   => 'integer',
    ];

    public function providers(){
        return $this->belongsTo(Office::class,'officeId','id')->withTrashed();
    }   
    public function document(){
        return $this->belongsTo(Document::class,'documentId','id')->withTrashed();
    }
    public function scopeMyDocument($query){
        // $user = auth()->user();
        // if($user->hasRole('admin') || $user->hasRole('demo_admin')) {
        //     $query =  $query;
        // }

        // if($user->hasRole('office')) {
        //     $query = $query->where('officeId', $user->id);
        // }

        return  $query->whereHas('document',function ($q) {
            $q->where('status',1);
        });
    }
    public function scopeList($query)
    {
        return $query->orderBy('updated_at', 'desc');
    }
}
