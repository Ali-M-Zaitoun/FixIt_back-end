<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Reply extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'complaint_id',
        'content',
        'sender_type',
        'sender_id'
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }
}
