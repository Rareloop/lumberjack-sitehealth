<?php

namespace Rareloop\Lumberjack\SiteHealth;

use Rareloop\Lumberjack\SiteHealth\HealthCheck;
use Rareloop\Lumberjack\Providers\ServiceProvider;

class SiteHealthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        collect(config('sitehealth.checks'))->each(function ($checkClass) {
            if (is_subclass_of($checkClass, HealthCheck::class)) {
                app($checkClass)->register();
            }
        });
    }
}
