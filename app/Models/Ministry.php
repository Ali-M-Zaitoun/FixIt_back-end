<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ministry extends Model
{
    protected $fillable = [
        'abbreviation',
        'status',
        'manager_id',
    ];

    public function branches()
    {
        return $this->hasMany(MinistryBranch::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function translations()
    {
        return $this->hasMany(MinistryTranslation::class);
    }

    public function translation($locale)
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }
}
