<?php


namespace Modules\ManageWork\Repositories\ManageWork;


interface ManageWorkRepositoryInterface
{
    /**
     * Kiểm tra và gửi nhắc nhở
     * @return mixed
     */
    public function sendRemindWork();

    /**
     * Tần suất lặp lại công việc
     * @return mixed
     */
    public function repeatWork();

    /**
     * Công việc hết hạn
     * @return mixed
     */
    public function workOverdue();

    /**
     * Thông báo tổng công việc trong ngày và tổng công việc quá hạn
     * @return mixed
     */
    public function workStartDay();

    /**
     * Xử lý thông báo nhân viên chưa bắt đầu công việc hoặc chưa có công việc trong ngày
     * @return mixed
     */
    public function workNotiEveryDay();
}