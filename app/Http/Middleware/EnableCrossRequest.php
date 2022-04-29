<?php


namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class EnableCrossRequest
{
    /**
     *
     * @param Request $request
     * @param Closure $next
     * Date : 2021/4/24 16:22
     * Author : 孤鸿渺影
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $origin = $request->server('HTTP_ORIGIN') ? $request->server('HTTP_ORIGIN') : '*';
        $allow_origin = systemConfig()->get('Sys.EnableCrossRequest', null) ?? config('EnableCrossRequest');

        if (!is_array($allow_origin)) {
            $allow_origin = json_decode($allow_origin, true);
        }

        if ($origin === '*' || in_array($origin, $allow_origin)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Allow-Headers', 'appid,x-xsrf-token,appSecret,X-File-Name,Origin, Access-Control-Request-Headers, SERVER_NAME, Access-Control-Allow-Headers,content-type, cache-control, token, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie,Authorization');
            $response->header('Access-Control-Expose-Headers', 'Authorization, authenticated');
            $response->header('Content-Security-Policy', 'upgrade-insecure-requests');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
        }
        return $response;
    }
}