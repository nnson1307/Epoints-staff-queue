<?php
namespace MyCore\Repository;

use Throwable;

/**
 * Class RepositoryTitleExceptionAbstract
 * @package MyCore\Repository
 * @author DaiDP
 * @since Jul, 2020
 */
abstract class RepositoryTitleExceptionAbstract extends \Exception
{
    protected $title;
    protected $error = [];
    protected $unkowError = [
        'message' => 'Xảy ra lỗi không xác định',
        'title'   => 'Lỗi không xác định'
    ];

    /**
     * PIIManageException constructor.
     * @param $errorType
     * @param array $preg
     */
    public function __construct($errorType, $preg = [])
    {
        $error = $this->error[$errorType] ?? $this->unkowError;
        $this->title = $error['title'];

        parent::__construct(__($error['message'], $preg), 1);
    }

    /**
     * Lấy tiêu đề thông báo
     *
     * @return string
     */
    public function getTitle()
    {
        return __($this->title);
    }
}