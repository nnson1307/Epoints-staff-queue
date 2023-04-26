<?php
namespace MyCore\Repository;

use Throwable;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 11/29/2018
 * Time: 9:44 AM
 */
abstract class RepositoryExceptionAbstract extends \Exception
{
    protected $errorData = null;


    public function __construct(string $message = "", int $code = 0, $errorData = null, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errorData = $errorData;
    }


    public function getErrorData()
    {
        return $this->errorData;
    }
}