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

class TickeProcessorTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'ticket_processor';
    protected $primaryKey = 'ticket_processor_id';

    /**
     * Lấy danh sách nhân viên xử lý
     */
    public function getListTicketProcessor($ticket_id){
        return $this
            ->select(
                $this->table.'.ticket_id',
                $this->table.'.name',
                $this->table.'.process_by',
                'staffs.email',
                'staffs.full_name as staff_name'
            )
            ->join('staffs','staffs.staff_id',$this->table.'.process_by')
            ->where('ticket_id',$ticket_id)
            ->get();
    }
}