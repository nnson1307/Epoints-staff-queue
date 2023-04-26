<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class StaffGroupTable extends Model
{

    protected $connection = BRAND_CONNECTION;
    protected $table = 'survey_group_staffs';
    protected $primaryKey = 'survey_group_staff_id';
    protected $fillable = [
        'survey_group_staff_id',
        'survey_id',
        'condition_branch',
        'condition_department',
        'condition_titile',
        'condition_type',
        'created_at',
        'updated_at'
    ];
    /**
     * danh sách điều kiện Query nhân viên
     * @return array
     */
    const conditionQueryStaff = [
        "condition_branch" => "Theo chi nhánh",
        "condition_department" => "Theo phòng ban",
        "condition_title" => "Theo chức vụ",
    ];

    /**
     * lấy danh sách điều kiện query theo nhân viên
     * @return [array]
     */

    public function getConditonQuery()
    {
        $result = self::conditionQueryStaff;
        return $result;
    }

}
