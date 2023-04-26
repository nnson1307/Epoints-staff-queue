<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use MyCore\Http\Response\ResponseFormatTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    use ResponseFormatTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
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
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            $errs = $exception->validator->errors()->toArray();
            $msg  = $this->getValidMsg($errs);

            if (is_array($msg)) {
                $msg = current($msg);
            }

            // log ra kibana
            Log::error($msg, $errs);

            return $this->responseJson(CODE_ERROR, $msg, $errs);
        };

        Log::error($exception->getMessage());

        if ($exception instanceof QueryException) {
            //return $this->responseJson(CODE_ERROR, 'Dữ liệu không đúng.');
            return $this->responseJson(CODE_ERROR, $exception->getMessage());
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->responseJson(CODE_ERROR, __('Bạn không có quyền truy cập.'));
        }

        return $this->responseJson(CODE_ERROR, $exception->getMessage() ?: __('Xảy ra lỗi. Vui lòng thử lại sau'));
    }

    /**
     * Lấy thông báo lỗi đầu tiên
     *
     * @param $errData
     * @return mixed|string
     */
    protected function getValidMsg($errData)
    {
        if (empty($errData)) {
            return 'Dữ liệu không đúng.';
        }

        return current($errData);
    }
}
