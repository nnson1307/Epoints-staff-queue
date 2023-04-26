<?php

/**
 * Created by PhpStorm.
 * User: SonVeratti
 * Date: 3/17/2018
 * Time: 1:26 PM
 */

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerGroupDefineDetailTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = 'customer_group_define_detail';
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'phone', 'customer_id', 'customer_code', 'user_group_id', 'created_at', 'updated_at'
    ];


    public function add(array $data)
    {
        $this->insert($data);
    }

    /**
     * Lấy danh sách khách theo id nhóm.
     * @param array $data
     * @return mixed
     */
    public function getDetail($id)
    {
        $oUser = $this->select(
            'customer_group_define_detail.id',
            'customers.email',
            'customer_group_define_detail.phone',
            'customer_group_define_detail.customer_id',
            'customer_group_define_detail.customer_code',
            'customer_group_define_detail.user_group_id'
        )
            ->leftJoin("customers", "customers.customer_id", "customer_group_define_detail.customer_id")
            ->where('customer_group_define_detail.user_group_id', $id)
            ->get();
        return $oUser;
    }

    public function removeByCustomerGroupId($id)
    {
        $oUser = $this->where('user_group_id', $id)->delete();
        return $oUser;
    }

    public function getCustomerInGroup($id)
    {
        $oUser = $this->select('customers.customer_id')
            ->join('customers', 'customers.phone1', 'customer_group_define_detail.phone')
            ->where('user_group_id', $id)
            ->get();
        return $oUser;
    }
    public function getCustomerIdInGroup($userGroupId)
    {
        $data = $this->select("customer_id")
            ->where('user_group_id', $userGroupId)->get()->toArray();
        return $data;
    }

    public function getIdInGroup($userGroupId)
    {
        $data = $this->where('user_group_id', $userGroupId)->get();
        return $data;
    }
}
