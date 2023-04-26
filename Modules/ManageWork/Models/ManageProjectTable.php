<?php
/**
 * Created by PhpStorm.
 * User: LE DANG SINH
 * Date: 9/26/2018
 * Time: 4:31 PM
 */

namespace Modules\ManageWork\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class ManageProjectTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_project';
    protected $primaryKey = 'manage_project_id';

    /**
     * Lấy danh sách tất cả dự án
     */
    public function getAll(){
        return $this
            ->whereNull('manage_project_status_id')
            ->get();
    }

    /**
     * check code
     * @param $code
     * @return mixed
     */
    public function checkCode($code){
        return $this
            ->where('prefix_code',$code)
            ->get();
    }

    public function updateProject($data,$projectId){
        return $this
            ->where('manage_project_id',$projectId)
            ->update($data);
    }
}