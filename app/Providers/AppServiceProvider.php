<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        if ($b64 = env('GOOGLE_CREDENTIALS_BASE64')) {
            $dest = storage_path('app/google-credentials.json');
            if (! file_exists($dest)) {
                @file_put_contents($dest, base64_decode($b64));
            }
        } elseif ($json = env('GOOGLE_CREDENTIALS_JSON')) {
            $dest = storage_path('app/google-credentials.json');
            if (! file_exists($dest)) {
                @file_put_contents($dest, $json);
            }
        }
    }
}
