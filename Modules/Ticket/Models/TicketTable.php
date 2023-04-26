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

class TicketTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'ticket';
    protected $primaryKey = 'ticket_id';

    /**
     * Lấy danh sách ticket quá hạn
     */
    public function getListTicketOverdue(){
        return $this
            ->select(
                $this->table.'.ticket_id',
                $this->table.'.ticket_code',
                $this->table.'.title as ticket_title',
                $this->table.'.date_expected',
                $this->table.'.step_warning',
                $this->table.'.step_warning_date',
                $this->table.'.date_issue',
                $this->table.'.operate_by',
                'customers.full_name as customer_name',
                'operate.email',
                'operate.full_name as operate_name',
                'ticket_request_type.name as ticket_request_type_name',
                $this->table.'.priority',
                'ticket_queue.queue_name'
            )
            ->join('ticket_queue','ticket_queue.ticket_queue_id',$this->table.'.queue_process_id')
            ->join('ticket_request_type','ticket_request_type.ticket_request_type_id',$this->table.'.ticket_type')
            ->join('customers','customers.customer_id',$this->table.'.customer_id')
            ->join('staffs as operate','operate.staff_id',$this->table.'.operate_by')
            ->whereIn($this->table.'.ticket_status_id',[1,2,3])
            ->where($this->table.'.date_expected','<',Carbon::now())
            ->get();
    }

    /**
     * Lấy danh sách ticket chưa phân công
     */
    public function getListTicketNotAssign(){
        return $this
            ->select(
                $this->table.'.ticket_id',
                $this->table.'.ticket_code',
                $this->table.'.date_expected',
                $this->table.'.title as ticket_title',
                $this->table.'.date_expected',
                $this->table.'.date_issue',
                $this->table.'.operate_by',
                $this->table.'.step_warning_assign',
                $this->table.'.step_warning_assign_date',
                $this->table.'.created_at',
                'ticket_processor.ticket_processor_id',
                'customers.full_name as customer_name',
                'operate.email',
                'operate.full_name as operate_name',
                'ticket_request_type.name as ticket_request_type_name',
                $this->table.'.priority',
                'ticket_queue.queue_name'
            )
            ->join('ticket_queue','ticket_queue.ticket_queue_id',$this->table.'.queue_process_id')
            ->join('ticket_request_type','ticket_request_type.ticket_request_type_id',$this->table.'.ticket_type')
            ->join('customers','customers.customer_id',$this->table.'.customer_id')
            ->join('staffs as operate','operate.staff_id',$this->table.'.operate_by')

            ->leftJoin('ticket_processor','ticket_processor.ticket_id',$this->table.'.ticket_id')
            ->whereNull('ticket_processor_id')
            ->get();
    }

    /**
     * Cập nhật ticket
     */
    public function updateTicket($data,$id){
        return $this->where('ticket_id',$id)->update($data);
    }
}