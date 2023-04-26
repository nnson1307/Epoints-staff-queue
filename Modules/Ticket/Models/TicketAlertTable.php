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

class TicketAlertTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'ticket_alert';
    protected $primaryKey = 'ticket_alert_id';

    /**
     * Lấy thông báo cấp cao nhất
     */
    public function getAlertDetail($step){
        return $this
            ->select(
                'template',
                'time',
                'ticket_role_queue_id',
                'is_noti',
                'is_email'
            )
            ->where('time_2',0)
            ->where('time_3',0)
            ->orderBy('ticket_alert_id','ASC')
            ->where('ticket_alert_id',$step)
            ->first();
    }

    /**
     * Lấy thông báo cấp cao nhất chưa phân công
     */
    public function getAlertDetailAssign(){
        return $this
            ->select(
                'time',
                'time_2',
                'time_3',
                'template',
                'ticket_role_queue_id',
                'is_noti',
                'is_email'
            )
            ->where('time_2','<>',0)
            ->where('time_3','<>',0)
            ->orderBy('ticket_alert_id','DESC')
            ->first();
    }
}