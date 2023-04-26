<?php
/**
 * Created by PhpStorm.
 * User: SonVeratti
 * Date: 3/17/2018
 * Time: 1:26 PM
 */

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroupDetailTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = 'customer_group_detail';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable
        = [
            'id',
            'customer_group_id',
            'group_type',
            'condition_rule',
            'condition_id',
            'customer_group_define_id',
            'day_appointment',
            'status_appointment',
            'time_appointment',
            'not_appointment',
            'use_service',
            'not_use_service',
            'use_product',
            'not_use_product',
            'not_order',
            'inactive_app',
            'use_promotion',
            'is_rank',
            'range_point',
            'top_high_revenue',
            'top_low_revenue',
            'top_low_revenue',
            'created_at',
            'updated_at'
        ];

    /**
     * Thêm mới
     * @param array $data
     */
    public function add(array $data)
    {
        $oSelect = $this->insert($data);
    }

    /**
     * Chi tiết nhóm tự động
     * @param $id
     * @return mixed
     */
    public function getDetail($id)
    {
        return $this->where('customer_group_id', $id)->get();
    }
    public function getDetailByType($id, $type)
    {
        return $this->where('customer_group_id', $id)
            ->where('group_type', $type)
            ->get();
    }

    public function removeAll($id)
    {
        return $this->where('customer_group_id', $id)->delete();
    }
}