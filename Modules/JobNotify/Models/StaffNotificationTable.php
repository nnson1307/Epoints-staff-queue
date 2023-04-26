<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 09/04/2021
 * Time: 14:16
 */

namespace Modules\JobNotify\Models;


use Illuminate\Database\Eloquent\Model;

class StaffNotificationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "staff_notification";
    protected $primaryKey = "staff_notification_id";
    protected $fillable = [
        "staff_notification_id",
        "staff_notification_detail_id",
        "user_id",
        "notification_avatar",
        "notification_title",
        "notification_message",
        "is_read",
        "staff_id",
        "branch_id"
    ];

    const IS_NEW = 0;
    const IS_OLD = 1;
    const NOT_READ = 0;
    const IS_READ = 1;

    /**
     * Thêm thông báo
     *
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        return $this->create($data)->staff_notification_id;
    }

    /**
     * Lấy danh sách thông báo
     *
     * @param $filter
     * @param $idUser
     * @return mixed
     */
    public function getNotifications($filter, $idUser)
    {
        $oSelect = $this->select(
            "{$this->table}.staff_notification_id",
            "{$this->table}.notification_title as title",
            "{$this->table}.notification_message as description",
            "{$this->table}.staff_notification_detail_id",
            "{$this->table}.is_read",
            "staff_notification_detail.action_name",
            "staff_notification_detail.action",
            "{$this->table}.created_at as created_date"
        )
            ->join("staff_notification_detail", "staff_notification_detail.staff_notification_detail_id", "=", "staff_notification.staff_notification_detail_id")
            ->where("{$this->table}.staff_id", $idUser)
            ->orderBy("{$this->table}.created_at", "desc");
        // get số trang
        $page = (int)($filter["page"] ?? 1);

        // filter theo is_read
        if (isset($filter["is_read"])) {
            $oSelect->where("is_read", $filter["is_read"]);
        }

        return $oSelect->paginate(PAGING_ITEM_PER_PAGE, $columns = ["*"], $pageName = "page", $page);
    }

    /**
     * Cập nhật trạng thái đã đọc thông báo
     * @param $idNotification
     * @param $idUser
     * @return integer
     */
    public function updateNotificationRead($idNotification, $idUser)
    {
        return $this->where($this->primaryKey, $idNotification)
            ->where("staff_id", $idUser)
            ->update(["is_read" => 1]);
    }

    /**
     * Lấy danh sách id notification theo user
     *
     * @param $idUser
     * @param $isBrand
     * @return array
     */
    public function getNotificationDetailIdByUser($idUser, $isBrand)
    {
        return $this->select("staff_notification_detail_id")
            ->where("user_id", $idUser)
            ->where("is_brand", $isBrand)
            ->get();
    }

    /**
     * Chi tiết thông báo
     *
     * @param $idNotification
     * @param $idUser
     * @return mixed
     */
    public function getNotificationDetail($idNotification, $idUser)
    {
        return $this->select(
            "{$this->table}.staff_notification_detail_id",
            "background",
            "content",
            "action_name",
            "action",
            "action_params",
            "notification_title"
        )
            ->join("staff_notification_detail", "{$this->table}.staff_notification_detail_id", "=", "staff_notification_detail.staff_notification_detail_id")
            ->where("staff_id", $idUser)
            ->where("staff_notification_id", $idNotification)
            ->first();
    }

    /**
     * Lấy thông báo theo user_id và notifcation_id
     * @param $idNotification
     * @param $idUser
     * @return array
     */
    public function getNotificationById($idNotification, $idUser)
    {
        return $this->where($this->primaryKey, $idNotification)
            ->where("staff_id", $idUser)
            ->first();
    }

    /**
     * Xóa thông báo theo id và user_id
     *
     * @param $idNotification
     * @param $idUser
     * @return mixed
     */
    public function deleteNotificationById($idNotification, $idUser)
    {
        return $this->where($this->primaryKey, $idNotification)
            ->where("staff_id", $idUser)
            ->delete();
    }

    /**
     * Đếm số lượng thông báo mới
     *
     * @param $userId
     * @return mixed
     */
    public function countNotification($userId)
    {
        return $this
            ->select(
                "staff_notification_id",
                "staff_id",
                "is_read"
            )
            ->where("staff_id", $userId)
            ->where("is_new", self::IS_NEW)
            ->get()
            ->count();
    }

    /**
     * Cập nhật tất cả thông báo mới thành cũ khi click vào chuông thông báo
     *
     * @param $userId
     * @return mixed
     */
    public function clearNotificationNew($userId)
    {
        return $this
            ->where("is_new", self::IS_NEW)
            ->where("staff_id", $userId)
            ->update(["is_new" => self::IS_OLD]);
    }

    /**
     * Đọc tất cả thông báo
     *
     * @param $userId
     * @return mixed
     */
    public function readAllNotification($userId)
    {
        return $this
            ->where("is_read", self::NOT_READ)
            ->where("staff_id", $userId)
            ->update(["is_read" => self::IS_READ]);
    }
}