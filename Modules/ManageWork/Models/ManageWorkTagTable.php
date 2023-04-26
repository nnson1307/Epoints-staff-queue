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
use MyCore\Models\Traits\ListTableTrait;

class ManageWorkTagTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'manage_work_tag';
    protected $primaryKey = 'manage_work_tag_id';

    public function getListTag($manage_work_id){
        return $this->select('manage_tag_id')->where('manage_work_id',$manage_work_id)->get();
    }

    public function insertTag($data){
        return $this->insert($data);
    }
}