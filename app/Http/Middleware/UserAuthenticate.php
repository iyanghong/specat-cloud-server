<?php


namespace App\Http\Middleware;

use App\Service\Auth\AuthService;
use Illuminate\Http\Request;
use Closure;

class UserAuthenticate
{

    /**
     *
     * @param Request $request
     * @param Closure $next
     * @date : 2021/5/27 20:16
     * @throws \App\Exceptions\NoLoginException
     * @author : 孤鸿渺影
     */
    public function handle(Request $request, Closure $next)
    {
//        return $next($request);
        /** @var $authenticate AuthService */
        $authenticate = app('Auth');
        $key = \Illuminate\Support\Facades\Route::currentRouteName();

        if ($key === null || $authenticate->check($key)) {
            return $next($request);
        }
        return response(api_response_action(false, $authenticate->getCode(), $authenticate->getMessage()));
    }
}