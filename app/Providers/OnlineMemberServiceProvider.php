<?php

namespace App\Providers;

use App\Service\OnlineMember\OnlineMemberService;
use Illuminate\Support\ServiceProvider;

class OnlineMemberServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('OnlineMember',function(){
            $OnlineMemberService = new OnlineMemberService();
            return $OnlineMemberService->handle(request());
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
