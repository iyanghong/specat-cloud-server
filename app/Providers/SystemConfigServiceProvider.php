<?php

namespace App\Providers;

use App\Service\SystemConfig\SystemConfigService;
use Illuminate\Support\ServiceProvider;

class SystemConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('SystemConfig',function (){
            $systemConfigService = new SystemConfigService('System');
            return $systemConfigService;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
