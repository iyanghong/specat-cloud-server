<?php
define('API_RESPONSE_SUCCESS', 200);
define('API_RESPONSE_UNAUTHORIZED', 401);
define('API_RESPONSE_NOT_FOUND', 404);
define('API_RESPONSE_SERVER_ERROR', 404);
if (!function_exists('api_response_action')) {
    function api_response_action(bool $isSuccess, ?int $code = null, string $message = '',?array $data = null):string
    {
        $apiResponse = new \App\Core\Utils\ApiResponse();
        $apiResponse->setSuccess($isSuccess);
        isset($code) && $apiResponse->setCode($code);
        !empty($message) && $apiResponse->setMessage($message);
        isset($data) && $apiResponse->setData($data);
        return $apiResponse->toString();
    }
}

if (!function_exists('api_response_show')) {
    function api_response_show($data,?int $code = null, string $message = ''):string
    {
        $apiResponse = new \App\Core\Utils\ApiResponse();
        $apiResponse->setSuccess(true);
        !empty($message) && $apiResponse->setMessage($message);
        if(isset($code)){
            $apiResponse->setCode($code);
        }else{
            if($data === null){
                $apiResponse->setCode(\App\Core\Enums\ErrorCode::$ENUM_NO_DATA_ERROR);
            }
        }
        $response = $apiResponse->toJson();
        $response['data'] = $data;
        return json_encode($response);
    }
}
if(!function_exists('api_response_list')){
    function api_response_list(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator) : string
    {
        $apiResponse = new \App\Core\Utils\ApiResponse();
        $apiResponse->setPaginate($paginator);
        return $apiResponse->toString();
    }
}
function api_response_paginate(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator) : string
{
    $apiResponse = new \App\Core\Utils\ApiResponse();
    $apiResponse->setPaginate($paginator);
    return $apiResponse->toString();
}
if(!function_exists('api_response')){
    function api_response($code, $msg = '', $data = [])
    {

    }
}
if(!function_exists('api_response_error')){
    function api_response_exception(\Exception $exception,$code = 500)
    {
        $apiResponse = new \App\Core\Utils\ApiResponse();
        $apiResponse->setSuccess(false);
        $apiResponse->setCode($code);
        $apiResponse->setMessage($exception->getMessage());
        $apiResponse->setLine("{$exception->getFile()}:{$exception->getLine()}");
        return $apiResponse->toString();
    }
}
