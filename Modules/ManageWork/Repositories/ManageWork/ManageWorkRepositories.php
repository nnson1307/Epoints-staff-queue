<?php


namespace Modules\ManageWork\Repositories\ManageWork;


use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\ManageWork\Models\ManageConfigNotificationTable;
use Modules\ManageWork\Models\ManageRedmindTable;
use Modules\ManageWork\Models\ManageRepeatTimeTable;
use Modules\ManageWork\Models\ManageWorkSupportTable;
use Modules\ManageWork\Models\ManageWorkTable;
use Modules\ManageWork\Models\ManageWorkTagTable;
use Modules\ManageWork\Models\StaffEmailLogTable;
use Modules\ManageWork\Models\StaffNotificationDetailTable;
use Modules\ManageWork\Models\StaffNotificationTable;
use Modules\ManageWork\Models\StaffTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;

class ManageWorkRepositories implements ManageWorkRepositoryInterface
{
    protected $mManageRemind;
    protected $mManageWork;
    protected $mManageRepeatTime;
    protected $staffNotificationDetail;
    protected $staffNotification;
    protected $request;
    protected $mManageWorkSupport;
    protected $mManageWorkTag;

    public function __construct(
        ManageRedmindTable $mManageRemind,
        StaffNotificationDetailTable $staffNotificationDetail,
        StaffNotificationTable $staffNotification,
        Request $request,
        ManageWorkTable $mManageWork,
        ManageRepeatTimeTable $mManageRepeatTime,
        ManageWorkSupportTable $mManageWorkSupport,
        ManageWorkTagTable $mManageWorkTag
    )
    {
        $this->mManageRemind = $mManageRemind;
        $this->staffNotification = $staffNotification;
        $this->staffNotificationDetail = $staffNotificationDetail;
        $this->request = $request;
        $this->mManageWork = $mManageWork;
        $this->mManageRepeatTime = $mManageRepeatTime;
        $this->mManageWorkSupport = $mManageWorkSupport;
        $this->mManageWorkTag = $mManageWorkTag;
    }

    public function sendRemindWork()
    {
//        Lấy danh sách nhắc nhở cần gửi
        $listRemind = $this->mManageRemind->getAllRemind();
        $mManageWork = app()->get(ManageWorkTable::class);
        $now = Carbon::now()->format('Y-m-d H:i:00');
        foreach ($listRemind as $item){
            $detail = $mManageWork->getDetailWork($item['manage_work_id']);
            if ($detail == null) {
                break;
            }
            $data = [
                'title' => $item['title'],
                'staff_id' => $item['staff_id'],
                'message' => $item['description'],
            ];

            if ($item['manage_work_id'] != null){
                $data['manage_work_id'] = $item['manage_work_id'];
            }

            if ($item['time_type'] == 'h'){
                if (Carbon::parse($item['date_remind'])->subHours($item['time'])->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')){
                    $this->replaceContentNoti($data);
                }
            } else if($item['time_type'] == 'd'){
                if (Carbon::parse($item['date_remind'])->subDays($item['time'])->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')){
                    $this->replaceContentNoti($data);
                }
            } else if($item['time_type'] == 'm'){
                if (Carbon::parse($item['date_remind'])->subMinutes($item['time'])->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')){
                    $this->replaceContentNoti($data);
                }
            }

            if(Carbon::parse($item['date_remind'])->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d H:i:00')){

                $this->mManageRemind->editRemind(['is_sent'=> 1],$item['manage_remind_id']);
                if ($item['is_active'] == 1) {
                    $this->replaceContentNoti($data);
                }
            }
        }
    }

    public function replaceContentNoti($data){
        if (isset($data['manage_work_id'])){
            $dataDetail = [
                'background' => '',
                'content' => $data['message'],
                'action_name' => 'Xem chi tiết',
                'action' => 'manage_work_detail',
                'action_params' => '{"manage_work_id":"'.$data['manage_work_id'].'"}',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        } else {
            $dataDetail = [
                'background' => isset($data['avatar']) ? $data['avatar'] : '',
                'content' => $data['message'],
                'action_name' => isset($data['detail_action_name']) ? $data['detail_action_name'] : '',
                'action' => isset($data['detail_action']) ? $data['detail_action'] : '',
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
        $dataMain->title =  isset($data['title']) ? $data['title'] : 'Nhắc nhở';
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
     * Tần suất lặp lại công việc
     * @return mixed|void
     */
    public function repeatWork()
    {
        $listWorkRepeat = $this->mManageWork->getListRepeat();
        $dataWork = [];
        $dayOfWeek = Carbon::now()->dayOfWeek == 0 ? 6 : Carbon::now()->dayOfWeek - 1;
        $dayOfMonth = (int)Carbon::now()->format('d');
        foreach ($listWorkRepeat as $item){
            $error = false;
            if ($item['repeat_type'] == 'daily'){
                if ($item['repeat_end'] == 'after'){
                    if (
                        ($item['repeat_end_type'] == 'd' && Carbon::parse($item['created_at'])->addDays($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'w' && Carbon::parse($item['created_at'])->addWeeks($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'm' && Carbon::parse($item['created_at'])->addMonths($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now())
                    ) {
                        $error = true;
                    }
                } else if ($item['repeat_end'] == 'date' && Carbon::parse($item['repeat_end_full_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now()){
                    $error = true;
                }
            } else if ($item['repeat_type'] == 'weekly'){
                if ($item['repeat_end'] == 'after'){
                    if (
                        ($item['repeat_end_type'] == 'd' && Carbon::parse($item['created_at'])->addDays($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'w' && Carbon::parse($item['created_at'])->addWeeks($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'm' && Carbon::parse($item['created_at'])->addMonths($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time'] == '' ? '00:00:00' : $item['repeat_time'])) < Carbon::now())
                    ) {
                        $error = true;
                    }
                } else if ($item['repeat_end'] == 'date' && Carbon::parse($item['repeat_end_full_time'])->format('Y-m-d '.($item['repeat_time']) == '' ? '00:00:00' : $item['repeat_time'] < Carbon::now())){
                    $error = true;
                }
            } else if ($item['repeat_type'] == 'monthly'){
                if ($item['repeat_end'] == 'after'){
                    if (
                        ($item['repeat_end_type'] == 'd' && Carbon::parse($item['created_at'])->addDays($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time']) == '' ? '00:00:00' : $item['repeat_time']) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'w' && Carbon::parse($item['created_at'])->addWeeks($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time']) == '' ? '00:00:00' :$item['repeat_time']) < Carbon::now()) ||
                        ($item['repeat_end_type'] == 'm' && Carbon::parse($item['created_at'])->addMonths($item['repeat_end_time'])->format('Y-m-d '.($item['repeat_time']) == '' ? '00:00:00' : $item['repeat_time']) < Carbon::now())
                    ) {
                        $error = true;
                    }
                } else if ($item['repeat_end'] == 'date' && Carbon::parse($item['repeat_end_full_time'])->format('Y-m-d '.($item['repeat_time']) == '' ? '00:00:00' : $item['repeat_time']) < Carbon::now()){
                    $error = true;
                }
            }

            if ($error == false){
//                dd(Carbon::now()->format('Y-m-d '.$item['repeat_time']));
                if ($item['repeat_type'] == 'daily' && (($item['repeat_time'] == '' && Carbon::now()->format('00:00:00') == '00:00:00') || ($item['repeat_time'] != '' && Carbon::now()->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d '.$item['repeat_time'])))){
                    if ($item['repeat_end'] != null && $item['repeat_end'] != 'none'){
                        $dataWork[] = $item['manage_work_id'];
                    } else {
                        if (Carbon::now()->format('H:i:00') < '00:10:00'){
                            $dataWork[] = $item['manage_work_id'];
                        }
                    }
                } else if ($item['repeat_type'] == 'weekly') {
                    $week = $this->mManageRepeatTime->getListRepeatTime($item['manage_work_id']);
                    $week = collect($week)->pluck('time')->toArray();

                    if (in_array($dayOfWeek,$week) && (($item['repeat_time'] == '' && Carbon::now()->format('00:00:00') == '00:00:00') || ($item['repeat_time'] != '' && Carbon::now()->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d '.$item['repeat_time'])))){
                        if ($item['repeat_end'] != null && $item['repeat_end'] != 'none'){
                            $dataWork[] = $item['manage_work_id'];
                        } else {
                            if (Carbon::now()->format('H:i:00') < '00:10:00'){
                                $dataWork[] = $item['manage_work_id'];
                            }
                        }

                    }
                } else if ($item['repeat_type'] == 'monthly') {
                    $monthly = $this->mManageRepeatTime->getListRepeatTime($item['manage_work_id']);
                    $monthly = collect($monthly)->pluck('time')->toArray();
                    if (in_array($dayOfMonth,$monthly) && (($item['repeat_time'] == '' && Carbon::now()->format('00:00:00') == '00:00:00') || ($item['repeat_time'] != '' && Carbon::now()->format('Y-m-d H:i:00') == Carbon::now()->format('Y-m-d '.$item['repeat_time'])))){
                        if ($item['repeat_end'] != null && $item['repeat_end'] != 'none'){
                            $dataWork[] = $item['manage_work_id'];
                        } else {
                            if (Carbon::now()->format('H:i:00') < '00:10:00'){
                                $dataWork[] = $item['manage_work_id'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($dataWork as $item){
            $dataItem = $this->mManageWork->getDetailWork($item);
            $dataItem['manage_status_id'] = 1;
            $dataItem['progress'] = 0;
            $dataItem['manage_work_code'] = $this->codeWork();
            $dataItem['created_at'] = Carbon::now();
            $dataItem['updated_at'] = Carbon::now();

            $idWork = $this->mManageWork->insertWork(collect($dataItem)->toArray());

            $listSupport = $this->mManageWorkSupport->getListRepeat($item);

            foreach ($listSupport as $keySupport => $itemSupport){
                $listSupport[$keySupport]['manage_work_id'] = $idWork;
                $listSupport[$keySupport]['created_at'] = Carbon::now();
                $listSupport[$keySupport]['updated_at'] = Carbon::now();
            }

            if (count($listSupport) != 0){
                $this->mManageWorkSupport->insertSupport(collect($listSupport)->toArray());
            }

            $listTag = $this->mManageWorkTag->getListTag($item);

            foreach ($listTag as $keyTag => $itemTag){
                $listTag[$keyTag]['manage_work_id'] = $idWork;
                $listTag[$keyTag]['created_at'] = Carbon::now();
                $listTag[$keyTag]['updated_at'] = Carbon::now();
            }

            if (count($listTag) != 0){
                $this->mManageWorkTag->insertTag(collect($listTag)->toArray());
            }

        }
    }

    public function codeWork(){
        $codeWork = 'CV_'.Carbon::now()->format('Ymd').'_';
        $workCodeDetail = $this->mManageWork->getCodeWork($codeWork);

        if ($workCodeDetail == null) {
            return $codeWork.'001';
        } else {
            $arr = explode($codeWork,$workCodeDetail);
            $value = strval(intval($arr[1]) + 1);
            $zero_str = "";
            if (strlen($value) < 7) {
                for ($i = 0; $i < (3 - strlen($value)); $i++) {
                    $zero_str .= "0";
                }
            }
            return $codeWork.$zero_str.$value;
        }

    }

    public function workOverdue()
    {

        $staffEmailLog = new StaffEmailLogTable();
        $mManageConfigNotification = new ManageConfigNotificationTable();
        $listOverdue = $this->mManageWork->getListOverdue();
        $configNoti = $mManageConfigNotification->getConfigByKey('work_expire');
        if ($configNoti['is_noti'] == 1 || $configNoti['is_mail'] == 1){
            $dataEmail = [];

            foreach ($listOverdue as $item){
                $this->mManageWork->updateWork(['is_overdue_noti' => 1],$item['manage_work_id']);

                $message = str_replace(['[manage_work_title]'], [$item['manage_work_title']], $configNoti['manage_config_notification_message']);

                if ($configNoti['is_noti'] == 1){
                    $listStaff = $this->insertNotificationLog($configNoti,$item);

                    foreach ($listStaff as $itemStaff){
                        $data = [
                            'title' => $configNoti['manage_config_notification_title'],
                            'staff_id' => $itemStaff,
                            'message' => $message,
                            'manage_work_id' => $item['manage_work_id'] != null ? $item['manage_work_id'] : '',
                            'detail_action_name' => $configNoti['detail_action_name'],
                            'detail_action' => $configNoti['detail_action']
                        ];
                        $this->replaceContentNoti($data);
                    }
                }

                if ($configNoti['is_mail'] == 1){

                    $listEmail = $this->addEmailLog($configNoti,$item);
                    $var = [
                        'content' => $message,
                        'title' => $configNoti['manage_config_notification_title']
                    ];
                    foreach ($listEmail as $itemEmail){
                        $dataEmail[] = [
                            'email_type' => $configNoti['manage_config_notification_key'],
                            'email_subject' => $configNoti['manage_config_notification_title'],
                            'email_from' => env('MAIL_USERNAME'),
                            'email_to' => $itemEmail,
                            'email_params' => json_encode($var),
                            'is_run' => 0,
                            'created_at' => Carbon::now()
                        ];
                    }

                }

            }
        }

        if (count($dataEmail) != 0){
            $staffEmailLog->addEmail($dataEmail);
        }
    }

    public function workStartDay(){
        $staff = new StaffTable();
        $listStaff = $staff->getAllStaff();
        $mManageConfigNotification = new ManageConfigNotificationTable();
        $notiWorkDay = $mManageConfigNotification->getConfigByKey('total_work_assign');
        $configNoti = $mManageConfigNotification->getConfigByKey('total_work_overdue');
        foreach($listStaff as $item){

//            Noti xtổng số công việc trong ngày
            $totalWorkDay = $this->mManageWork->getTotalWorkInDay($item);
            if ($totalWorkDay != null && $totalWorkDay['total_work_day'] != 0){
//                Công việc trong ngày
                $title = str_replace(['[total_work]'], [$totalWorkDay['total_work_day']], $notiWorkDay['manage_config_notification_title']);
                $message = str_replace(['[staff_name]','[total_work]'], [$item['full_name'],$totalWorkDay['total_work_day']], $notiWorkDay['manage_config_notification_message']);
                $data = [
                    'title' => $title,
                    'staff_id' => $item['staff_id'],
                    'message' => $message,
                    'detail_action_name' => $notiWorkDay['detail_action_name'],
                    'detail_action' => $notiWorkDay['detail_action']
                ];

                $this->replaceContentNoti($data);
            }

            if ($totalWorkDay != null && $totalWorkDay['total_overdue'] != 0){
//                Công việc trong ngày
                $title = str_replace(['[total_work]'], [$totalWorkDay['total_overdue']], $configNoti['manage_config_notification_title']);
                $message = str_replace(['[staff_name]','[total_work]'], [$item['full_name'],$totalWorkDay['total_overdue']], $configNoti['manage_config_notification_message']);
                $data = [
                    'title' => $title,
                    'staff_id' => $item['staff_id'],
                    'message' => $message,
                    'detail_action_name' => $configNoti['detail_action_name'],
                    'detail_action' => $configNoti['detail_action']
                ];
                $this->replaceContentNoti($data);
            }
        }
    }

    private function addEmailLog($configNoti,$info) {
        try {
            $listEmail = [];
            if ($configNoti['is_mail'] == 1){
                $mManageWorkSupport = new ManageWorkSupportTable();
                $listStaff = [];
                if ($configNoti['is_created'] == 1){
                    $listStaff[$info['created_by']] = $info['created_by'];
                }

                if ($configNoti['is_processor'] == 1){
                    $listStaff[$info['processor_id']] = $info['processor_id'];
                }

                if ($configNoti['is_approve'] == 1){
                    $listStaff[$info['approve_id']] = $info['approve_id'];
                }

                if ($configNoti['is_support'] == 1){
                    $listSupport = $mManageWorkSupport->getListStaffByWork($info['manage_work_id']);
                    if (count($listSupport) != 0){
                        $listSupport = collect($listSupport)->pluck('staff_id')->toArray();
                        $listStaff = array_merge($listStaff,$listSupport);
                    }
                }

                $listStaff = array_unique($listStaff);

                $staff = new StaffTable();

                $listEmail = $staff->getListStaffByArrId($listStaff);

                if (count($listEmail) != 0){
                    $listEmail = collect($listEmail)->pluck('email')->toArray();
                }
            }

            return $listEmail;

        } catch (\Exception $exception) {
            return [];
        }
    }

    private function insertNotificationLog($configNoti,$info)
    {
        try {
            $listStaff = [];
            if ($configNoti['is_noti'] == 1){
                $mManageWorkSupport = new ManageWorkSupportTable();

                if ($configNoti['is_created'] == 1){
                    $listStaff[$info['created_by']] = $info['created_by'];
                }

                if ($configNoti['is_processor'] == 1){
                    $listStaff[$info['processor_id']] = $info['processor_id'];
                }

                if ($configNoti['is_approve'] == 1){
                    $listStaff[$info['approve_id']] = $info['approve_id'];
                }

                if ($configNoti['is_support'] == 1){
                    $listSupport = $mManageWorkSupport->getListStaffByWork($info['manage_work_id']);
                    if (count($listSupport) != 0){
                        $listSupport = collect($listSupport)->pluck('staff_id')->toArray();
                        $listStaff = array_merge($listStaff,$listSupport);
                    }
                }
            }

            return $listStaff;
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * Thông báo nhân viên chưa bắt đầu công việc hoặc chưa có công việc trong ngày
     * @return mixed|void
     */
    public function workNotiEveryDay()
    {
        try {
            $staff = new StaffTable();
            $listStaff = $staff->getAllStaff();

            $mManageConfigNotification = new ManageConfigNotificationTable();
            $work_staff_not_started_yet = $mManageConfigNotification->getConfigByKey('work_staff_not_started_yet');
            $work_staff_no_work = $mManageConfigNotification->getConfigByKey('work_staff_no_work');
            $work_operator_not_started_yet = $mManageConfigNotification->getConfigByKey('work_operator_not_started_yet');
            $work_operator_no_work = $mManageConfigNotification->getConfigByKey('work_operator_no_work');

            foreach($listStaff as $item){
//                Tổng số công việc trong ngày
                $totalWorkDay = $this->mManageWork->getTotalWorkInDay($item);

//                Chưa có công việc trong ngày
                if ($totalWorkDay == null || $totalWorkDay['total_work_day'] == 0){

                    $title = $work_staff_no_work['manage_config_notification_title'];
                    $message = $work_staff_no_work['manage_config_notification_message'];
                    $data = [
                        'title' => $title,
                        'staff_id' => $item['staff_id'],
                        'message' => $message,
                    ];
                    $this->replaceContentNoti($data);
                }

//                Chưa bắt đầu công việc trong ngày

                if ($totalWorkDay != null && $totalWorkDay['total_work_day'] != 0 && $totalWorkDay['total_start'] == 0 ){

                    $title = $work_staff_not_started_yet['manage_config_notification_title'];
                    $message = $work_staff_not_started_yet['manage_config_notification_message'];
                    $data = [
                        'title' => $title,
                        'staff_id' => $item['staff_id'],
                        'message' => $message,
                    ];
                    $this->replaceContentNoti($data);
                }
            }
        } catch (\Exception $exception) {
            return [];
        }
    }
}