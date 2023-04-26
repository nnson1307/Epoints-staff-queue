<?php
namespace MyCore\SyncStrava;

use Throwable;

/**
 * Class SyncActivityException
 * @package MyCore\SyncStrava
 * @author DaiDp
 * @since Aug, 2019
 */
class SyncActivityException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}