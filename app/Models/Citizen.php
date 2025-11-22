<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Citizen extends Model
{
    protected $fillable = [
        'user_id',
        'nationality',
        'national_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function image()
    {
        return $this->morphOne(Media::class, 'mediable')->where('type', 'img');
    }

    public function replies()
    {
        return $this->morphMany(Reply::class, 'sender');
    }
}
