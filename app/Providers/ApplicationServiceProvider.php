<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DriveService;
use App\Services\BaseDriveService;
use App\Services\ApplicationService;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register any Google Drive application services.
     */
    public function register()
    {
        $folderId = getenv('GOOGLE_APPLICATIONS_FOLDER_ID');
        // dd($folderId);
        $this->app->singleton(ApplicationService::class, function ($app) use ($folderId) {
            return new ApplicationService($folderId);
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

