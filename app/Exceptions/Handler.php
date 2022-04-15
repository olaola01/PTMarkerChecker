<?php

namespace App\Exceptions;

use App\Enum\ApiStatusMessageResponse;
use App\Traits\ApiResponder;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponder;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception): \Symfony\Component\HttpFoundation\Response
    {
        if ($exception instanceof  ValidationException){
            return $this->convertValidationExceptionToResponse($exception,$request);
        }

        if ($exception instanceof HttpException){
            return $this->badCall($exception->getStatusCode(),ApiStatusMessageResponse::ERROR, $exception->getMessage());
        }

        return $this->badCall(500,ApiStatusMessageResponse::ERROR, $exception->getMessage());

    }

    public function convertValidationExceptionToResponse(ValidationException $e, $request): Response|JsonResponse|RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $errors = $e->validator->errors()->getMessages();

        return $this->badCall(422,ApiStatusMessageResponse::ERROR, $errors);
    }
}
