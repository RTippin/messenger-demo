<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Symfony\Component\HttpFoundation\Response;
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
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     * @throws Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response|JsonResponse|RedirectResponse|Redirector
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        switch(class_basename($exception)){
            case 'TokenMismatchException':
                if ($request->ajax()){
                    return response()->json(['error' => 66, 'errors' => ['forms' => 'Your request was denied. Please try again or reload your page']], 403);
                }
                return redirect(route('login'))->with('modal.title', 'Session Expired!')->with('modal.msg', 'Your session expired. Please login again.');
            break;
            case 'ThrottleRequestsException':
                return response()->json(['errors' => ['forms' => 'You have been rate limited, please try again shortly']], 429);
            break;
            case 'MethodNotAllowedHttpException':
                if ($request->ajax()){
                    return response()->json(['errors' => ['forms' => 'Method Not Allowed']],405);
                }
                return redirect()->route('messages');
            break;
            case 'NotFoundHttpException':
                if ($request->ajax()){
                    return response()->json(['errors' => ['forms' => 'We could not locate the data you requested, it may have been lost forever']],404);
                }
                return parent::render($request, $exception);
            break;
            case 'MaintenanceModeException':
                if ($request->ajax()){
                    return response()->json(['errors' => ['forms' => 'The site is currently down for maintenance, please check back with us soon']],503);
                }
                return parent::render($request, $exception);
            break;
            case 'AuthenticationException':
            case 'ValidationException':
                return parent::render($request, $exception);
            break;
        }
        if (app()->isProduction()){
            if ($request->ajax()){
                return response()->json('Server Error',500);
            }
            return response()->view('errors.500', [], 500);
        }
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  Request  $request
     * @param AuthenticationException $exception
     * @return RedirectResponse|JsonResponse
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        session()->flash('info_message', 'Please login to continue');
        if ($request->expectsJson()) {
            return response()->json(['error' => 01, 'errors' => ['forms' => 'Unauthenticated']], 401);
        }
        return redirect()->guest(route('login'));
    }
}
