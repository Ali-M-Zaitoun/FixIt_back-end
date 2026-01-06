<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Governorate extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['code'];

    public function translations()
    {
        return $this->hasMany(GovernorateTranslation::class);
    }

    public function translation($locale)
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }

    public function MinistryBranches()
    {
        return $this->hasMany(MinistryBranch::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
    }
}
