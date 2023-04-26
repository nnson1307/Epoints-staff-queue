<?php
namespace Modules\Notification\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class StaffTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    /**
     * Lấy danh sách user active
     *
     * @return mixed
     */
    public function getUserActive()
    {
        return $this->select(
                        $this->primaryKey
                    )
                    ->where('is_activated', 1)
                    ->get();
    }
}
