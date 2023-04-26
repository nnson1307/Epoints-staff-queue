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

class TicketRequestMaterialTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = "ticket_request_material";
    protected $primaryKey = "ticket_request_material_id";

//    Tạo phiếu yêu cầu
    public function createdRequestForm($data){
        return $this->insertGetId($data);
    }

    public function updateRequestForm($data,$ticketRequestMaterialId){
        return $this->where('ticket_request_material_id',$ticketRequestMaterialId)->update($data);
    }

//    Lấy danh sách phiếu yêu cầu
    public function listRequestMaterial($data){
        $oSelect = $this
            ->select(
                $this->table.'.ticket_request_material_id',
                $this->table.'.ticket_request_material_code',
                $this->table.'.ticket_id',
                $this->table.'.proposer_by',
                'staffs.full_name as proposer_name',
                $this->table.'.proposer_date',
                $this->table.'.description',
//                $this->table.'.status',
                DB::raw('IF(ticket_request_material.status = "new","Mới",IF(ticket_request_material.status = "approve","Đã duyệt","Từ chối")) as status'),
                DB::raw("IF(ticket_request_material.status = 'new','Mới',IF(ticket_request_material.status = 'approve','Đã duyệt','Từ chối')) as status_name")
            )
            ->join('staffs','staffs.staff_id',$this->table.'.proposer_by');

        if (isset($data['ticket_id'])) {
            $oSelect = $oSelect->where('ticket_id',$data['ticket_id']);
        }

        if (isset($data['arr_ticket_id'])) {
            $oSelect = $oSelect->whereIn('ticket_id',$data['arr_ticket_id']);
        }

        return $oSelect->orderBy($this->table.'.ticket_request_material_id')->get();
    }

//    Lấy chi tiết phiếu yêu cầu
    public function getDetail($ticket_request_material_id){
        $oSelect = $this
            ->select(
                $this->table.'.ticket_request_material_id',
                $this->table.'.ticket_request_material_code',
                $this->table.'.ticket_id',
                $this->table.'.proposer_by',
                'staffs.full_name as proposer_name',
                $this->table.'.proposer_date',
                $this->table.'.approved_by',
                'staffs_approved.full_name as approved_name',
                $this->table.'.approved_date',
                $this->table.'.description',
//                $this->table.'.status',
                DB::raw('IF(ticket_request_material.status = "new","Mới",IF(ticket_request_material.status = "approve","Đã duyệt","Từ chối")) as status'),
                DB::raw('IF(ticket_request_material.status = "new","Mới",IF(ticket_request_material.status = "approve","Đã duyệt","Từ chối")) as status_name')
            )
            ->join('staffs','staffs.staff_id',$this->table.'.proposer_by')
            ->leftJoin('staffs as staffs_approved','staffs_approved.staff_id',$this->table.'.approved_by')
            ->where('ticket_request_material_id',$ticket_request_material_id)
            ->first();
        return $oSelect;
    }

//    Xoá phiếu yêu cầu
    public function deleteForm($ticketRequestMaterialId){
        $oSelect = $this
            ->where('ticket_request_material_id',$ticketRequestMaterialId)
            ->delete();
        return $oSelect;
    }

//    Lấy phiếu yêu cầu mới nhất được tạo trong ngày
    public function getRequestFormNew($code){
        $oSelect = $this
            ->where('ticket_request_material_code','like','%'.$code.'%')
            ->orderBy('ticket_request_material_id','DESC')
            ->first();

        return $oSelect != null ? $oSelect['ticket_request_material_code'] : null;
    }

//    Lấy thông tin chi tiết cho noti
    public function getDetailForNoti($ticket_request_material_id){
        $oSelect = $this
            ->select(
                $this->table.'.ticket_id',
                $this->table.'.ticket_request_material_id',
                $this->table.'.ticket_request_material_code',
                'ticket.ticket_code',
                'staffs.full_name as full_name_updated'

            )
            ->join('ticket','ticket.ticket_id',$this->table.'.ticket_id')
            ->join('staffs','staffs.staff_id',$this->table.'.updated_by')
            ->where($this->table.'.ticket_request_material_id',$ticket_request_material_id)
            ->first();
        return $oSelect;
    }
}