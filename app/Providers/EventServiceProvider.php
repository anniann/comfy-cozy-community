<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DriveService;
use App\Services\BaseDriveService;
use App\Services\EventService;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any Google Drive application services.
     */
    public function register()
    {
        $folderId = getenv('GOOGLE_EVENTS_FOLDER_ID');
        // dd($folderId);
        $this->app->singleton(EventService::class, function ($app) use ($folderId) {
            return new EventService($folderId);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //
    }
}

