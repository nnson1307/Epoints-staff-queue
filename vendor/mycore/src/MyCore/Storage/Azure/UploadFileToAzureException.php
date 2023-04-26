<?php
namespace MyCore\Storage\Azure;

use Exception;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/19/2019
 * Time: 10:36 AM
 */
class UploadFileToAzureException extends Exception
{
    const FILE_NOT_FOUND = 1;
    const FILE_TOO_LARGE = 2;
    const FILE_NOT_ALLOW = 3;
    const FILE_UPLOAD_FAILED = 4;

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

            case self::FILE_UPLOAD_FAILED :
                return 'Upload ảnh thất bại';
            default:
                return null;
        }
    }
}