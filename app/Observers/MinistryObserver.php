<?php

namespace App\Observers;

use App\Models\Ministry;

class MinistryObserver
{
    public function created(Ministry $ministry): void
    {
        //
    }

    public function updated(Ministry $ministry): void
    {
        //
    }

    public function deleted(Ministry $ministry): void
    {
        $ministry->branches()->delete();

        $ministry->complaints()->delete();
    }

    public function restored(Ministry $ministry): void
    {
        $ministry->branches()->restore();
    }

    public function forceDeleted(Ministry $ministry): void
    {
        //
    }
}
