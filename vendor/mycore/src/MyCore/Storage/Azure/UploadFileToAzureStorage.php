<?php
namespace MyCore\Storage\Azure;

//require_once "../vendor/autoload.php";
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

/**
 * Class AuthJwtStorage
 * @package MyCore\Storage\Redis
 * @author DaiDP
 * @since Aug, 2019
 */
class UploadFileToAzureStorage implements UploadFileToAzureManager
{
    protected $maxSize = 20 * 1048576; // MB
    protected $allowMineType = [
        'image/gif',
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
    ];
    protected $blobClient;

    public function __construct()
    {
        $this->blobClient = BlobRestProxy::createBlobService(env('AZ_STORAGE_CONSTR'));
    }

    /**
     * Upload file
     * @param $file
     * @return mixed
     * @throws UploadFileToAzureException
     */
    public function doUpload($file)
    {
        // Kiểm tra kích thước
        $fileSize = $file->getSize();

        if ($fileSize > $this->maxSize) {
            throw new UploadFileToAzureException(UploadFileToAzureException::FILE_TOO_LARGE);
        }

        // Kiểm tra mine type
        $mineTYpe = $file->getMimeType();
        if (!in_array($file->getMimeType(), $this->allowMineType)) {
            throw new UploadFileToAzureException(UploadFileToAzureException::FILE_NOT_ALLOW);
        }

        //Mở stream file
        $handle = @fopen($file, "r");
        $fileName =  uniqid() . '_' . $file->getClientOriginalName();

        if ($handle) {
            try {
                //Set loại file upload lên Azure Blob
                $options = new CreateBlockBlobOptions();
                $options->setContentType($mineTYpe);

                //Upload lên Azure Blob
                $this->blobClient->createBlockBlob(CONTAINER_NAME, $fileName, $handle, $options);

                //Tìm kiếm file uploaded theo $fileName
                $listBlobsOptions = new ListBlobsOptions();
                $listBlobsOptions->setPrefix($fileName);
                $blob_list = $this->blobClient->listBlobs(CONTAINER_NAME, $listBlobsOptions);
                $blobs = $blob_list->getBlobs();

                //Không tim thấy file vừa upload
                if (sizeof($blobs) <= 0) {
                    throw new UploadFileToAzureException(UploadFileToAzureException::FILE_UPLOAD_FAILED);
                }

                //Đóng Stream file
                @fclose($handle);

                return [
                    'public_path' => $blobs[0]->getUrl(),
                    'file_name' => $fileName,
                    'file_size' => $fileSize,
                    'mine_type' => $mineTYpe
                ];
            } catch (\Exception $ex) {
                throw new UploadFileToAzureException(UploadFileToAzureException::FILE_UPLOAD_FAILED);
            }
        } else {
            throw new UploadFileToAzureException(UploadFileToAzureException::FILE_UPLOAD_FAILED);
        }
    }

    /**
     * Xóa hình ở Blob
     * @param $file_path
     * @return mixed
     */
    public function deleteBlobImage($file_path)
    {
        try {
            $fileName = trim(basename($file_path));
            if(!empty($fileName)){
                $this->blobClient->deleteBlob(CONTAINER_NAME, $fileName);
            }
        } catch (ServiceException $e) {
        }
    }
}
