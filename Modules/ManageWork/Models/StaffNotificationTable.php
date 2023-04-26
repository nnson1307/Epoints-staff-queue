<?php
namespace Modules\ManageWork\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class StaffNotificationTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_notification';
    protected $primaryKey = 'staff_notification_id';

    protected $fillable = [
        'staff_notification_detail_id', 'user_id', 'notification_avatar', 'notification_title',
        'notification_message', 'notification_type', 'is_read', 'staff_id'
    ];

    /**
     * Thêm notification vào db
     *
     * @param $idUser
     * @param null $detailId
     * @param $title
     * @param $message
     * @param null $avatar
     * @return
     */
    public function addNotify($idUser, $title, $message, $detailId = null, $avatar = null)
    {
        return self::create([
            'staff_id' => $idUser,
            'notification_detail_id' => $detailId,
            'notification_title' => $title,
            'notification_message' => $message,
            'notification_avatar' => $avatar,
            'is_read' => 0
        ]);
    }

    /**
     * Đếm số lượng notification chưa đọc
     *
     * @param $idUser
     * @return mixed
     */
    public function countUnreadNotify($idUser)
    {
        return $this->where('staff_id', $idUser)
                    ->where('is_read', 0)
                    ->count();
    }
}
