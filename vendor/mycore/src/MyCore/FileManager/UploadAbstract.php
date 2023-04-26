<?php
namespace MyCore\FileManager;

use Carbon\Carbon;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/19/2019
 * Time: 10:30 AM
 */
abstract class UploadAbstract
{
    protected $inputName     = 'upload_file';
    protected $maxSize       = 20 * 1048576; // MB
    protected $allowMineType = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png'
    ];

    protected function getUploadPath()
    {

    }


    /**
     * Khoi tao folder
     *
     * @param $path
     * @return array
     */
    protected function initDir($path)
    {
        $date = Carbon::now()->format('Y-m-d');

        $fullPath = $path . DIRECTORY_SEPARATOR . $date;

        if (! file_exists($fullPath)) {
            mkdir($fullPath,0755, true);
        }

        return $date;
    }
}