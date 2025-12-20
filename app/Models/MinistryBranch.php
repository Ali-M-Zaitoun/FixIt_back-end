<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MinistryBranch extends Model
{
    protected $fillable = [
        'ministry_id',
        'governorate_id',
        'manager_id',
    ];

    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function translations()
    {
        return $this->hasMany(MinistryBranchTranslation::class);
    }

    public function translation($locale)
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
