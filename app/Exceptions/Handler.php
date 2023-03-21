<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use League\OAuth2\Server\Exception\OAuthServerException;
use Throwable;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function report(Throwable $exception)
    {
        if ($exception instanceof OAuthServerException && ($exception->getMessage() == 'The user credentials were incorrect.' || $exception->getCode() == 9)) {
            return;
        }

        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Illuminate\Http\Response
     */
    // public function render($request, Throwable $exception)
    // {
    //     if (
    //         !empty($exception)
    //         and
    //         $request->expectsJson() || $request->is('api/v*')
    //     ) {
    //         if ($exception instanceof ValidationException) {
    //             return response()->json([
    //                 'error' => $exception->validator->errors()->first()
    //             ], 422);

    //         } else if ($this->isHttpException($exception)) {
    //             return response()->json([
    //                 'error' => 'Request error'
    //             ], $exception->getStatusCode());

    //         } else {
    //             $status = method_exists($exception, 'getStatusCode') ?
    //                 $exception->getStatusCode()
    //                 :
    //                 400;

    //             return response()->json([
    //                 'error' => $exception->getMessage(),
    //                 'trace' => 
    //             ], $status);
    //         }
    //     }

    //     return parent::render($request, $exception);
    // }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
