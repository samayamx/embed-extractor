<?php

namespace Samaya\Embed;

use Illuminate\Support\ServiceProvider;

class OEmbedExtractProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
              __DIR__.'/../config/config.php' => config_path('embed-extractor.php'),
            ], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'embed-extractor');

        $this->app->singleton(Extract::class, function () {
            $userAgent = config('embed-extractor.userAgent');
            $fbAppId = config('embed-extractor.fb.appId');
            $fbSecret = config('embed-extractor.fb.secret');

            return new Extract($userAgent, $fbAppId, $fbSecret);
        });
    }
}
