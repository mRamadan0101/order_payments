<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        RequiredConstraintsViolated::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
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
        $this->reportable(function (\Throwable $e) {
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $exception)
    {
        if ($exception instanceof \InvalidArgumentException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 400, 'message' => 'check Request.'], 400);
            } else {
                return response()->json(['status' => 403, 'message' => 'Unauthenticated.'], 403);
            }
        }
        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 403, 'message' => 'You do not have the required authorization.'], 403);
            } else {
                return response()->json(['status' => 403, 'message' => 'You do not have the required authorization.'], 403);
            }
        }

        if ($exception instanceof MethodNotAllowedHttpException || $exception instanceof UnauthorizedHttpException) {
            if ($request->expectsJson()) {
                return response()->json(['status' => 403, 'message' => 'Forbidden.'], 403);
            } else {
                return response()->json(['status' => 403, 'message' => 'Forbidden.'], 403);
            }
        }
        if ($exception instanceof TooManyRequestsHttpException) {
            // if ($request->expectsJson()) {
            return response()->json(['status' => 429, 'message' => 'too many requests.'], 429);
            // }
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 401, 'message' => 'Unauthenticated.'], 401);
        } elseif (is_array($request->route()->computedMiddleware) && in_array('auth:api', $request->route()->computedMiddleware)) {
            return response()->json(['status' => 401, 'message' => 'you must login first, api-token required'], 401);
        } else {
            return response()->json(['status' => 401, 'message' => 'Unauthenticated.'], 401);
        }
    }
}
