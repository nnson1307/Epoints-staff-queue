<?php
namespace MyCore\FileManager;
use Throwable;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/19/2019
 * Time: 10:36 AM
 */
class UploadException extends \Exception
{
    const FILE_NOT_FOUND = 1;
    const FILE_TOO_LARGE = 2;
    const FILE_NOT_ALLOW = 3;

    public function __construct(int $code = 0, string $message = "")
    {
        parent::__construct($message ?: $this->transMessage($code), $code);
    }


    protected function transMessage($code)
    {
        switch ($code)
        {
            case self::FILE_NOT_FOUND :
                return 'Không tìm thấy file';

            case self::FILE_TOO_LARGE :
                return 'Kích thước file quá lớn';

            case self::FILE_NOT_ALLOW :
                return 'File không đúng định dạng';

            default:
                return null;
        }
    }
}