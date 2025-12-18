<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Reply;
use App\Policies\ComplaintPolicy;
use App\Policies\ReplyPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'employee' => 'App\Models\Employee',
            'citizen' => 'App\Models\Citizen',
            'complaint' => 'App\Models\Complaint'
        ]);

        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(Reply::class, ReplyPolicy::class);
    }
}
