<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use Notifiable, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ministry_id',
        'ministry_branch_id',
        'start_date',
        'end_date',
        'promoted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(MinistryBranch::class, 'ministry_branch_id');
    }

    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    public function replies()
    {
        return $this->morphMany(Reply::class, 'sender');
    }

    public function canAccessComplaint($complaint)
    {
        return ($this->ministry_branch_id === $complaint->ministry_branch_id)
            || ($this->user && $this->user->hasAnyRole(['super_admin', 'ministry_manager']));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }
}
