<?php
namespace MyCore\SMS;

use Throwable;

/**
 * Class SMSException
 * @package MyCore\SMS
 * @author DaiDP
 * @since Aug, 2019
 */
class SMSException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}