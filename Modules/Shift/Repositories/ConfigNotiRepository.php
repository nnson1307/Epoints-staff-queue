<?php


namespace Modules\Shift\Repositories;


use Carbon\Carbon;
use Modules\ManageWork\Models\StaffNotificationDetailTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;
use Modules\Shift\Models\SfTimekeepingNotificationTable;
use Modules\Shift\Models\SfTimeWorkingStaffsTable;

class ConfigNotiRepository implements ConfigNotiRepositoryInterface
{

    protected $staffNotificationDetail;

    public function __construct(StaffNotificationDetailTable $staffNotificationDetail)
    {
        $this->staffNotificationDetail = $staffNotificationDetail;
    }

//    Nhắc nhở nhân viên checkin
    public function remindCheckIn()
    {
        $mSfTimekeepingNoti = app()->get(SfTimekeepingNotificationTable::class);
        $mSfTimeWorkingStaff = app()->get(SfTimeWorkingStaffsTable::class);
        $listConfig = $mSfTimekeepingNoti->getAll();
        $nowDate = Carbon::now()->format('Y-m-d');
        $data['date_now'] = $nowDate;
        foreach ($listConfig as $item){
            $time = Carbon::now();
            if ($item['type_send'] == 0){
                $time = Carbon::now()->format('H:i:00');
            } else if ($item['type_send'] == 1){
                $time = Carbon::now()->addMinutes($item['time_send'])->format('H:i:00');
            } else if ($item['type_send'] == 2){
                $time = Carbon::now()->subMinutes($item['time_send'])->format('H:i:00');
            }

            $data['time_now'] = $time;
            $data['type_check'] = $item['type'];

//            Lấy danh sách nhân viên chưa checkin , checkout phù hợp điều kiện
            $listStaff = $mSfTimeWorkingStaff->getListStaff($data);
            foreach ($listStaff as $itemStaff) {
                $item['staff_id'] = $itemStaff['staff_id'];
                $this->replaceContentNoti($item);
            }
        }
    }

    public function replaceContentNoti($data){
        $dataDetail = [
            'background' => isset($data['avatar']) ? $data['avatar'] : '',
            'content' => $data['sf_timekeeping_notification_content'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];

//        insert chi tiết noti
        $detailId = $this->staffNotificationDetail->insertNotiDetail($dataDetail);

        $dataMain = new \stdClass();

        $dataMain->tenant_id = session()->get('idTenant');
        $dataMain->staff_id = $data['staff_id'];
        $dataMain->detail_id = $detailId;
        $dataMain->title =  $data['sf_timekeeping_notification_title'];
        $dataMain->message = $data['sf_timekeeping_notification_content'];
        $dataMain->avatar = $data['avatar'];
        $dataMain->schedule = '';
        $dataMain->notification_type = 'default';
        $dataMain->background = null;

        $message = new UnicastMessage((array)$dataMain);
        $notiDetailRepo = app()->get(PushNotificationRepo::class);
        $notiDetailRepo->unicast($message);
    }
}