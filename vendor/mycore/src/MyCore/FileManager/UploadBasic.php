<?php
namespace MyCore\FileManager;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/19/2019
 * Time: 10:29 AM
 */
class UploadBasic extends UploadAbstract
{
    protected $allowMineType = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/pdf'
    ];

    public function doUpload()
    {
        $request = request();

        //  Kiểm tra có file không
        if (!$request->hasFile($this->inputName)) {
            throw new UploadException(UploadException::FILE_NOT_FOUND);
        }

        // Kiểm tra kích thước
        $file = $request->{$this->inputName};
        $fileSize = $file->getSize();
        if ($fileSize > $this->maxSize) {
            throw new UploadException(UploadException::FILE_TOO_LARGE);
        }

        // Kiểm tra mine type
        $mineTYpe = $file->getMimeType();
        if (!in_array($file->getMimeType(), $this->allowMineType)) {
            throw new UploadException(UploadException::FILE_NOT_ALLOW);
        }

        $fileName = uniqid() . '_' . $file->getClientOriginalName();
        $file->move(REAL_TMP_PATH, $fileName);

        return [
            'file_name' => $fileName,
            'file_path' => REAL_TMP_PATH . $fileName,
            'file_size' => $fileSize,
            'mine_type' => $mineTYpe,
            'public_path' => asset(TEMP_PATH . $fileName)
        ];
    }


    /**
     * @param $tmpFileName
     * @param $baseDir
     * @return string
     */
    public function storage($tmpFileName, $baseDir)
    {
        $path = $this->initDir($baseDir);
        $storagePath = $path . '/' . $tmpFileName;

        @copy(REAL_TMP_PATH . $tmpFileName, $baseDir . DIRECTORY_SEPARATOR . $storagePath);
        @unlink(REAL_TMP_PATH . $tmpFileName);

        return '/' . $storagePath;
    }
}