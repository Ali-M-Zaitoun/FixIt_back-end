<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
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
}
