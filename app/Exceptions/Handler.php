<?php

namespace App\Exceptions;

use App\Core\Enums\ErrorCode;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        /* 错误页面 */
        if ($exception instanceof NotFoundHttpException && $request->method() !== 'GET') {
            $code = $exception->getStatusCode();

            if ($code === 403) {
                return response(api_response_action(false, 403, '访问被拒绝'));
            }
            if ($code === 404) {
                return response(api_response_action(false, 404, '找不到该接口地址'));
            }
        }
        if($exception instanceof NoLoginException){
            $apiResponse = new \App\Core\Utils\ApiResponse();
            $apiResponse->setSuccess(false);
            $apiResponse->setCode(ErrorCode::$ENUM_NO_LOGIN_ERROR);
            $apiResponse->setMessage($exception->getMessage());

            return response($apiResponse->toString());
        }
        if($exception instanceof \Exception){
            $apiResponse = new \App\Core\Utils\ApiResponse();
            $apiResponse->setSuccess(false);
            $apiResponse->setCode(500);
            $apiResponse->setMessage($exception->getMessage());
            if ($exception instanceof \ErrorException) {
                $apiResponse->setLine("{$exception->getFile()}:{$exception->getLine()}");
            }
            return response($apiResponse->toString());
        }
        return parent::render($request, $exception);
    }
}
