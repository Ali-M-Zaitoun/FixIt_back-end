<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Complaint extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'type',
        'description',
        'status',
        'governorate_id',
        'city_name',
        'street_name',
        'citizen_id',
        'ministry_id',
        'ministry_branch_id',
        'notes',
        'locked_by',
        'locked_at'
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function citizen()
    {
        return $this->belongsTo(Citizen::class)->withTrashed();
    }

    public function ministry()
    {
        return $this->belongsTo(Ministry::class)->withTrashed();
    }

    public function ministryBranch()
    {
        return $this->belongsTo(MinistryBranch::class)->withTrashed();
    }

    public function lockedEmployee()
    {
        return $this->belongsTo(Employee::class, 'locked_by');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class)->withTrashed();
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function isArabic($attribute): bool
    {
        return (bool) preg_match('/^\p{Arabic}/u', $this->$attribute);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }
}
