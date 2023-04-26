<?php

/**
 * Created by PhpStorm
 * User: Mr Son
 * Date: 15-04-02020
 * Time: 3:44 PM
 */

namespace Modules\Survey\Models;


use Illuminate\Database\Eloquent\Model;

class NotificationTemplateAutoTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = "notification_template_auto";
    protected $primaryKey = "id";
    protected $fillable = [
        "id",
        "key",
        "title",
        "message",
        "avatar",
        "has_detail",
        "detail_background",
        "detail_content",
        "detail_action_name",
        "detail_action",
        "detail_action_params",
        "created_at",
        "updated_at"
    ];
    const KEY_NOTIFI_SURVEY_POINT = 'survey_point';

    /**
     * Thêm notification template auto
     *
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $add = $this->create($data);
        return $add->id;
    }

    /**
     * Chỉnh sửa notification template auto
     *
     * @param array $data
     * @param $key
     * @return mixed
     */
    public function edit(array $data, $key)
    {
        return $this->where("key", $key)->update($data);
    }

    /**
     * lấy item notification theo key
     * @param $keyNotifi
     * @return mixed
     */

    public function getItemByKey($key)
    {
        return $this->where("key", $key)->first();
    }
}
