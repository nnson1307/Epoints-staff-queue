<?php
/**
 * Created by PhpStorm.
 * User: LE DANG SINH
 * Date: 9/26/2018
 * Time: 4:31 PM
 */

namespace Modules\Ticket\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use MyCore\Models\Traits\ListTableTrait;

class StaffEmailLogTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'staff_email_log';
    protected $primaryKey = 'staff_email_log_id';

    /**
     * Tạo log email
     * @return mixed
     */
    public function createEmailLog($data){
        return $this->insert($data);
    }

}