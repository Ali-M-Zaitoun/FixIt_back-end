<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
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
}
