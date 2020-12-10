<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use RTippin\Messenger\Exceptions\InvalidMessengerProvider;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        InvalidMessengerProvider::class,
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
     * @param Request $request
     * @param Throwable $exception
     * @return JsonResponse
     * @throws Throwable
     * @noinspection PhpMissingParamTypeInspection
     */
    public function render($request, Throwable $exception)
    {
        if($exception instanceof ModelNotFoundException){
            return new JsonResponse([
                'message' => "Unable to locate the {$this->prettyModelNotFound($exception)} you requested."
            ], 404);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Throwable $exception
     * @return string
     * @noinspection PhpPossiblePolymorphicInvocationInspection
     */
    private function prettyModelNotFound(Throwable $exception): string
    {
        try {
            if( ! is_null($exception->getModel()))
            {
                return Str::lower(
                    ltrim(
                        preg_replace(
                            '/[A-Z]/',
                            ' $0',
                            (new ReflectionClass($exception->getModel()))->getShortName()
                        )
                    )
                );
            }
        } catch (ReflectionException $e) {
            report($e);
        }
        return 'resource';
    }
}
