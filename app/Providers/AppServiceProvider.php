<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\Ministry;
use App\Models\Reply;
use App\Observers\MinistryObserver;
use App\Policies\ComplaintPolicy;
use App\Policies\ReplyPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'Employee' => 'App\Models\Employee',
            'Citizen' => 'App\Models\Citizen',
            'Complaint' => 'App\Models\Complaint',
            'Refresh Token' => 'App\Models\RefreshToken',
            'Ministry' => 'App\Models\Ministry',
            'Ministry Branch' => 'App\Models\MinistryBranch',
            'Reply' => 'App\Models\Reply',
        ]);

        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(Reply::class, ReplyPolicy::class);

        Activity::creating(function (Activity $activity) {
            if (app()->bound('trace_id')) {
                $traceId = app('trace_id');
                $activity->trace_id = $traceId;

                $activity->properties = $activity->properties->put('trace_id', $traceId);
            }
        });

        Ministry::observe(MinistryObserver::class);

        if ($this->app->environment('production')) {
            URL::forceHttps('https');
        }
    }
}
