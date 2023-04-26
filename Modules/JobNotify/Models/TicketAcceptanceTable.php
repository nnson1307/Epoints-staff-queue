<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/05/2021
 * Time: 14:37
 */

namespace Modules\JobNotify\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TicketAcceptanceTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "ticket_acceptance";
    protected $primaryKey = "ticket_acceptance_id";

//    Lấy danh sách file theo loại
    public function getDetail($ticket_id){
        $oSelect = $this
            ->select(
                $this->table.'.ticket_acceptance_id',
                $this->table.'.ticket_acceptance_code',
                $this->table.'.title',
                $this->table.'.sign_by',
                $this->table.'.sign_date',
//                'staffs.full_name as sign_name',
                $this->table.'.customer_id',
                $this->table.'.status',
                DB::raw('CONCAT(customers.full_name," - ", customers.phone1 ) as customer_name')
            )
//            ->leftJoin('staffs','staffs.staff_id',$this->table.'.sign_by')
            ->leftJoin('customers','customers.customer_id',$this->table.'.customer_id')
            ->where($this->table.'.ticket_id',$ticket_id)
            ->first();
        return $oSelect;
    }

//    Tạo biên bản nghiệm thu
    public function createdTicketAcceptance($data){
        return $this->insertGetId($data);
    }

//    Chỉnh sửa biên bản nghiệm thu
    public function editTicketAcceptance($data,$id){
        return $this->where('ticket_acceptance_id',$id)->update($data);
    }

    public function getAcceptanceNew($code){
        $oSelect = $this
            ->where('ticket_acceptance_code','like','%'.$code.'%')
            ->orderBy('ticket_acceptance_id','DESC')
            ->first();

        return $oSelect != null ? $oSelect['ticket_acceptance_code'] : null;
    }

//    Lấy thông tin chi tiết cho noti
    public function getDetailForNoti($ticketId){
        $oSelect = $this
            ->select(
                $this->table.'.ticket_id',
                $this->table.'.ticket_acceptance_id',
                'ticket.ticket_code',
                $this->table.'.ticket_acceptance_code',
                'staffs.full_name as full_name_updated'
            )
            ->join('ticket','ticket.ticket_id',$this->table.'.ticket_id')
            ->join('staffs','staffs.staff_id',$this->table.'.updated_by')
            ->where($this->table.'.ticket_id',$ticketId)
            ->first();
        return $oSelect;
    }
}