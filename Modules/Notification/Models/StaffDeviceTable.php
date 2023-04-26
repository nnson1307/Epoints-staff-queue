<?php
namespace Modules\Notification\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DeviceTokenTable
 * @package Modules\Notification\Models
 * @author DaiDP
 * @since Aug, 2020
 */
class StaffDeviceTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_device';
    protected $primaryKey = 'staff_device_id';
    public $timestamps = false;

    protected $fillable = ['staff_id', 'imei', 'model', 'platform',  'os_version', 'app_version',
        'token', 'date_created', 'last_access', 'date_modified', 'modified_by', 'created_by',
        'is_actived', 'is_deleted','endpoint_arn'];

    /**
     * Add device token
     *
     * @param $data
     * @param $endpointArn
     * @return mixed
     */
    public function addDevice($data, $endpointArn)
    {
        $data['is_actived'] = 1;
        $data['date_created'] = Carbon::now();
        $data['last_access'] = Carbon::now();
        $data['date_modified'] = Carbon::now();
        $data['endpoint_arn'] = $endpointArn;
        $data['modified_by'] = $data['staff_id'];
        $data['created_by'] = $data['staff_id'];


        return self::create($data);
    }

    /**
     * Lấy thông tin token
     *
     * @param $idUser
     * @param $imei
     * @param $platform
     * @return mixed
     */
    public function getInfo($idUser, $imei, $platform)
    {
        return $this->where('staff_id', $idUser)
                    ->where('platform', $platform)
                    ->where('imei', $imei)
                    ->first();
    }

    /**
     * Cập nhật thông tin đăng ký
     *
     * @param $id
     * @param $deviceToken
     * @param $endpointArn
     * @return mixed
     */
    public function updateToken($id, $deviceToken, $endpointArn)
    {
        return $this->where($this->primaryKey, $id)
                    ->update([
                        'is_actived'   => 1,
                        'date_modified' => Carbon::now(),
                        'last_access' => Carbon::now(),
                        'token' => $deviceToken,
                        'endpoint_arn' => $endpointArn
                    ]);
    }

    /**
     * Lấy danh sách thiết bị của user
     *
     * @param $idUser
     * @return mixed
     */
    public function getUserDevice($idUser)
    {
        return $this->select(
                        'token',
                        'platform',
                        'endpoint_arn'
                    )
                    ->where('staff_id', $idUser)
                    ->where('is_actived', 1)
                    ->whereNotNull('endpoint_arn')
                    ->get();
    }
}
