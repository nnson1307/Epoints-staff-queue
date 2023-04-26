## Upload file
## Cài đặt Azure SDK

```bash
composer require Azure/azure-storage-php
```
## Cấu hình
Add the constants to <span style='color:red'>.env</span> as the follows:
```bash

CONNECTION_STRING=DefaultEndpointsProtocol=https;AccountName=<account_name>;AccountKey=<account_key>

CONTAINER_NAME=<>

```

Add this line to <span style="color:red;">Modules/<module_name>/Providers/RepositoryServiceProvider.php</span>
```bash
$this->app->singleton(UploadFileToAzureManager::class, UploadFileToAzureStorage::class);
```
Well done.

## Class and Interface
```bash
MyCore\Storage\Azure\UploadFileToAzureManager::class;
MyCore\Storage\Azure\UploadFileToAzureStorage::class;
```
## Upload file to Azure Manager
Basic use:
```bash
protected $uploadManager;
public function __construct(UploadFileToAzureManager $uploadManager)
{
   $this->uploadManager = $uploadManager;
}
```
### Methods
The following methods are available on the UploadFileToAzure instance.

#### doUpload()
Upload file lên Azure Blob Store
```bash
$file: request file
$result = $uploadManager->doUpload($file);

$result struct:
[
    'public_path' => <file_url>,
    'file_name' => <file_name>,
    'file_size' => <file_size>,
    'mine_type' => <file_mine_type>
]
```

#### deleteBlobImage()
Xóa file ở Azure Blob Store
```bash
$file_path: url file
$result = $uploadManager->deleteBlobImage($file_path);
```
