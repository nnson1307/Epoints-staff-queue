<?php


namespace Modules\Ticket\Repositories\Ticket;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\ManageWork\Models\StaffNotificationDetailTable;
use Modules\ManageWork\Models\StaffNotificationTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;
use Modules\Ticket\Models\StaffEmailLogTable;
use Modules\Ticket\Models\TickeProcessorTable;
use Modules\Ticket\Models\TicketAlertTable;
use Modules\Ticket\Models\TicketTable;

class TicketRepository implements TicketRepositoryInterface
{

    protected $ticket;
    protected $ticketAlert;
    protected $staffNotificationDetail;
    protected $staffNotification;
    protected $ticketProcessor;
    protected $request;

    public function __construct(
        TicketTable $ticket,
        TicketAlertTable $ticketAlert,
        StaffNotificationDetailTable $staffNotificationDetail,
        StaffNotificationTable $staffNotification,
        TickeProcessorTable $ticketProcessor,
        Request $request
    )
    {
        $this->ticket = $ticket;
        $this->ticketAlert = $ticketAlert;
        $this->staffNotificationDetail = $staffNotificationDetail;
        $this->staffNotification = $staffNotification;
        $this->ticketProcessor = $ticketProcessor;
        $this->request = $request;
    }

    /**
     * Ticket quá hạn
     * @return mixed|void
     */
    public function ticketOverdue()
    {
        $mStaffEmailLog = new StaffEmailLogTable();
//        Quá hạn
        $listTicket = $this->ticket->getListTicketOverdue();
        $title = 'Ticket quá hạn';
        $dataEmail = [];
        foreach ($listTicket as $item){
            $step = $item['step_warning'] + 1;
            $detailAlert = $this->ticketAlert->getAlertDetail($step);
            if ($detailAlert != null){
//                foreach ($listAlert as $detailAlert){
                    $n = $detailAlert['time'];

                    if($item['step_warning'] == 0){
                        $date = Carbon::parse($item['date_expected'])->format('Y-m-d H:i:00');
                        $time = Carbon::parse($item['date_expected'])->addMinutes($n)->format('Y-m-d H:i:00');
                    } else {
                        $date = Carbon::parse($item['step_warning_date'])->format('Y-m-d H:i:00');
                        $time = Carbon::parse($item['step_warning_date'])->addMinutes($n)->format('Y-m-d H:i:00');
                    }

                    if($detailAlert['is_noti'] == 1 || $detailAlert['is_email'] == 1){
                        if ($date <= Carbon::now()->format('Y-m-d H:i:00')){
                            $dataUpdate = [
                                'step_warning' => $item['step_warning'] + 1,
                                'step_warning_date' => $time
                            ];
                            $this->ticket->updateTicket($dataUpdate,$item['ticket_id']);
                            $getListProcessor = $this->ticketProcessor->getListTicketProcessor($item['ticket_id']);
//                            Gửi theo noti
                            if($detailAlert['is_noti'] == 1){
                                if ($detailAlert['ticket_role_queue_id'] == 2 && $item['operate_by'] != null){
                                    $data = [
                                        'staff_id' => $item['operate_by'],
                                        'message' => $detailAlert['template'],
                                        'ticket_id' => $item['ticket_id'],
                                        'title' => $title
                                    ];
                                    $this->replaceContentNoti($data);
                                }

                                if ($detailAlert['ticket_role_queue_id'] == 1){
                                    foreach ($getListProcessor as $itemProcessor){
                                        $data = [
                                            'staff_id' => $itemProcessor['process_by'],
                                            'message' => $detailAlert['template'],
                                            'ticket_id' => $item['ticket_id'],
                                            'title' => $title
                                        ];
                                        $this->replaceContentNoti($data);
                                    }
                                }
                            }

//                            Thông báo bằng email
                            if($detailAlert['is_email'] == 1){
                                $listStaff = [];
//                                    Danh sách nhân viên xử lý
                                foreach ($getListProcessor as $itemStaff){
                                    $listStaff[] = [
                                        'staff_name' => $itemStaff['staff_name']
                                    ];
                                }

                                if ($detailAlert['ticket_role_queue_id'] == 2 && $item['operate_by'] != null){
                                    $var = [
                                        'ticket_code' => $item['ticket_code'],
                                        'customer' => $item['customer_name'],
                                        'ticket_title' => $item['ticket_title'],
                                        'ticket_request_type_name' => $item['ticket_request_type_name'],
                                        'priority' => $item['priority'] == 'H' ? 'Cao' : ($item['priority'] == 'L' ? 'Bình thường' : 'Thấp'),
                                        'date_issue' => $item['date_issue'],
                                        'date_expected' => $item['date_expected'],
                                        'queue_name' => $item['queue_name'],
                                        'operate_name' => $item['operate_name'],
                                        'processor_name' => $listStaff,
                                    ];
                                    if ($item['email'] != null){
                                        $dataEmail[] = [
                                            'email_type' => 'ticket_overdue',
                                            'email_subject' => $title,
                                            'email_from' => env('MAIL_USERNAME'),
                                            'email_to' => $item['email'],
                                            'email_params' => json_encode($var),
                                            'is_run' => 0,
                                            'created_at' => Carbon::now()
                                        ];
                                    }
                                }

                                if ($detailAlert['ticket_role_queue_id'] == 1){
                                    foreach ($getListProcessor as $itemProcessor){
                                        $var = [
                                            'ticket_code' => $item['ticket_code'],
                                            'customer' => $item['customer_name'],
                                            'ticket_title' => $item['ticket_title'],
                                            'ticket_request_type_name' => $item['ticket_request_type_name'],
                                            'priority' => $item['priority'] == 'H' ? 'Cao' : ($item['priority'] == 'L' ? 'Bình thường' : 'Thấp'),
                                            'date_issue' => $item['date_issue'],
                                            'date_expected' => $item['date_expected'],
                                            'queue_name' => $item['queue_name'],
                                            'operate_name' => $item['operate_name'],
                                            'processor_name' => $listStaff,
                                        ];

                                        $dataEmail[] = [
                                            'email_type' => 'ticket_overdue',
                                            'email_subject' => $title,
                                            'email_from' => env('MAIL_USERNAME'),
                                            'email_to' => $itemProcessor['email'],
                                            'email_params' => json_encode($var),
                                            'is_run' => 0,
                                            'created_at' => Carbon::now()
                                        ];
                                    }
                                }
                            }
                        }
                    }
//                }
            }
        }
        if (count($dataEmail) != 0){
            $mStaffEmailLog->createEmailLog($dataEmail);
        }
    }

    public function replaceContentNoti($data){
        if (isset($data['ticket_id'])){
            $dataDetail = [
                'background' => '',
                'content' => $data['message'],
                'action_name' => 'Xem chi tiết',
                'action' => 'ticket_detail',
                'action_params' => '{"ticket_id":"'.$data['ticket_id'].'"}',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        } else {
            $dataDetail = [
                'background' => '',
                'content' => $data['message'],
                'action_name' => '',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

//        insert chi tiết noti
        $detailId = $this->staffNotificationDetail->insertNotiDetail($dataDetail);

        $dataMain = new \stdClass();

        $dataMain->tenant_id = session()->get('idTenant');
        $dataMain->staff_id = $data['staff_id'];
        $dataMain->detail_id = $detailId;
        $dataMain->title = $data['title'];
        $dataMain->message = $data['message'];
        $dataMain->avatar = '';
        $dataMain->schedule = '';
        $dataMain->notification_type = 'default';
        $dataMain->background = null;

        $message = new UnicastMessage((array)$dataMain);
        $notiDetailRepo = app()->get(PushNotificationRepo::class);
        $notiDetailRepo->unicast($message);

    }

    /**
     * Ticket chưa phân công
     * @return mixed|void
     */
    public function ticketNotAssign()
    {
        $listTicket = $this->ticket->getListTicketNotAssign();
        $mStaffEmailLog = new StaffEmailLogTable();
        $title = 'Ticket chưa phân công';
        $dataEmail = [];
        foreach ($listTicket as $item){
            $detailAlert = $this->ticketAlert->getAlertDetailAssign();
            if ($detailAlert != null){
                if(($detailAlert['is_noti'] == 1 || $detailAlert['is_email'] == 1) && $item['step_warning_assign'] < 3 ){

                    if($item['step_warning_assign'] == 0){
                        $date = Carbon::parse($item['created_at'])->format('Y-m-d H:i:00');
                        $time = Carbon::parse($item['created_at'])->addMinutes($detailAlert['time'])->format('Y-m-d H:i:00');
                    } else {
                        $date = Carbon::parse($item['step_warning_assign_date'])->format('Y-m-d H:i:00');
                        if($item['step_warning_assign'] == 1){
                            $time = Carbon::parse($item['step_warning_assign_date'])->addMinutes($detailAlert['time_2'])->format('Y-m-d H:i:00');
                        } else if ($item['step_warning_assign'] == 2) {
                            $time = Carbon::parse($item['step_warning_assign_date'])->addMinutes($detailAlert['time_3'])->format('Y-m-d H:i:00');
                        }
                    }
                    $nowDate = Carbon::now()->format('Y-m-d H:i:00');

                    if ($date < $nowDate){

                        $dataUpdate = [
                            'step_warning_assign' => $item['step_warning_assign'] + 1,
                            'step_warning_assign_date' => $time
                        ];

                        $this->ticket->updateTicket($dataUpdate,$item['ticket_id']);

                        if (($detailAlert['ticket_role_queue_id'] == 2 || $detailAlert['ticket_role_queue_id'] == '') && $item['operate_by'] != null){

                            if ($detailAlert['is_email'] == 1){
                                $var = [
                                    'ticket_code' => $item['ticket_code'],
                                    'customer' => $item['customer_name'],
                                    'ticket_title' => $item['ticket_title'],
                                    'ticket_request_type_name' => $item['ticket_request_type_name'],
                                    'priority' => $item['priority'] == 'H' ? 'Cao' : ($item['priority'] == 'L' ? 'Bình thường' : 'Thấp'),
                                    'date_issue' => $item['date_issue'],
                                    'date_expected' => $item['date_expected'],
                                    'queue_name' => $item['queue_name'],
                                    'operate_name' => $item['operate_name'],
                                ];
                                if ($item['email'] != null){
                                    $dataEmail[] = [
                                        'email_type' => 'ticket_assign',
                                        'email_subject' => $title,
                                        'email_from' => env('MAIL_USERNAME'),
                                        'email_to' => $item['email'],
                                        'email_params' => json_encode($var),
                                        'is_run' => 0,
                                        'created_at' => Carbon::now()
                                    ];
                                }
                            }

                            if ($detailAlert['is_noti'] == 1){
                                $data = [
                                    'staff_id' => $item['operate_by'],
                                    'message' => $detailAlert['template'],
                                    'ticket_id' => $item['ticket_id'],
                                    'title' => $title
                                ];
                                $this->replaceContentNoti($data);
                            }
                        }
                    }
                }
            }
        }

        if (count($dataEmail) != 0){
            $mStaffEmailLog->createEmailLog($dataEmail);
        }
    }
}