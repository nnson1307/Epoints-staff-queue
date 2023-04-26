<?php
namespace MyCore\Storage\Azure;

/**
 * Interface UploadFileToAzureManager
 * @package MyCore\Storage\Azure
 * @author BangNB
 * @since Sep, 2019
 */
interface UploadFileToAzureManager
{
    /**
     * Upload file
     * @param $file
     * @return mixed
     */
    public function doUpload($file);

    /**
     * Xóa hình ở Blob
     * @param $file_path
     * @return mixed
     */
    public function deleteBlobImage($file_path);
}