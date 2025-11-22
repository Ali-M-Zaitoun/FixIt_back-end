<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'reference_number',
        'type',
        'description',
        'status',
        'governorate_id',
        'city_name',
        'street_name',
        'citizen_id',
        'ministry_branch_id',
        'locked_by',
        'locked_at'
    ];


    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    public function ministryBranch()
    {
        return $this->belongsTo(MinistryBranch::class);
    }

    public function lockedEmployee()
    {
        return $this->belongsTo(Employee::class, 'locked_by');
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
