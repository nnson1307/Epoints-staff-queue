<?php
namespace MyCore\GraphQL\Format;

use Error as PhpError;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use GraphQL\Error\Debug;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use Rebing\GraphQL\Error\ValidationError;
use Rebing\GraphQL\Error\AuthorizationError;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 19/12/2019
 * Time: 9:13 AM
 */
class MyHandle
{
    /**
     * @see \GraphQL\Executor\ExecutionResult::setErrorFormatter
     * @param  Error  $e
     * @return array
     */
    public static function formatError(Error $e): array
    {
        $debug = config('app.debug') ? (Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE) : 0;
        $formatter = FormattedError::prepareFormatter(null, $debug);
        $error = $formatter($e);

        $previous = $e->getPrevious();
        if ($previous && $previous instanceof ValidationError) {
            $error['extensions']['validation'] = $previous->getValidatorMessages();
        }

        return [
            'ErrorCode' => $e->getCode() ?: 1,
            'ErrorDescription' => $e->message,
            'ErrorData' => ($previous && $previous instanceof ValidationError) ? $previous->getValidatorMessages() : []
        ];

        //return $error;
    }

    /**
     * @param  Error[]  $errors
     * @param  callable  $formatter
     * @return Error[]
     */
    public static function handleErrors(array $errors, callable $formatter): array
    {
        $handler = app()->make(ExceptionHandler::class);
        foreach ($errors as $error) {
            // Try to unwrap exception
            $error = $error->getPrevious() ?: $error;

            // Don't report certain GraphQL errors
            if ($error instanceof ValidationError
                || $error instanceof AuthorizationError
                || ! (
                    $error instanceof Exception
                    || $error instanceof PhpError
                )) {
                continue;
            }

            if (! $error instanceof Exception) {
                $error = new FatalThrowableError($error);
            }

            $handler->report($error);
        }

        return array_map($formatter, $errors);
    }
}