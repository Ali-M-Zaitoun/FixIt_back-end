<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ministry extends Model
{
    use LogsActivity, SoftDeletes;
    protected $fillable = [
        'abbreviation',
        'status',
        'manager_id',
    ];

    public function branches()
    {
        return $this->hasMany(MinistryBranch::class)->withTrashed();
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id')->withTrashed();
    }

    public function translations()
    {
        return $this->hasMany(MinistryTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class)->withTrashed();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }
}
