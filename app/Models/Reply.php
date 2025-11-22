<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    protected $fillable = [
        'complaint_id',
        'content',
        'sender_type',
        'sender_id'
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function sender()
    {
        return $this->morphTo();
    }
}
