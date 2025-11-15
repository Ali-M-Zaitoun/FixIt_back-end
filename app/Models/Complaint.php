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
        'citizen_id',
        'location',
        'ministry_branch_id',
        'governorate_id',
    ];

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function images()
    {
        return $this->media()->where('type', 'img');
    }

    public function files()
    {
        return $this->media()->where('type', 'file');
    }

    public function citizen()
    {
        return $this->belongsTo(Citizen::class);
    }

    public function ministryBranch()
    {
        return $this->belongsTo(MinistryBranch::class);
    }
}
