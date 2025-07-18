<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($this->isHttpException($e)) {
            $statusCode = $e->getStatusCode();

            // Check if custom error view exists
            if (view()->exists("errors.{$statusCode}")) {
                return response()->view("errors.{$statusCode}", [
                    'exception' => $e
                ], $statusCode);
            }
        }

        return parent::render($request, $e);
    }
}
