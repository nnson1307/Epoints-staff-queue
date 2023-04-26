<?php


namespace Modules\Survey\Repositories\Survey;


use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Survey\Models\StaffsTable;
use Modules\Survey\Models\SurveyTable;
use Modules\Survey\Models\CustomerTable;
use Modules\Survey\Models\SurveyAnswerTable;
use Modules\Survey\Jobs\SaveNotificationSurvey;
use Modules\Survey\Models\SurveyConfigPointTable;
use Modules\Survey\Models\CustomerGroupFilterTable;
use Modules\Survey\Jobs\SendNotificationPointSurvey;
use Modules\Survey\Models\CustomerGroupDefineDetailTable;
use Modules\Survey\Repositories\Survey\SurveyRepositoryInterface;
use Modules\Survey\Repositories\Customer\CustomerGroupFilterRepository;


class SurveyRepository implements SurveyRepositoryInterface
{
    protected $mSurvey;
    public function __construct(SurveyTable $mSurvey)
    {
        $this->mSurvey = $mSurvey;
    }
    /**
     * lấy tất cả các user áp dụng trong khảo sát
     * @param $itemSurvey
     * @return array
     */
    public function getAllUserApply($itemSurvey)
    {

        $typeUser = $itemSurvey->type_user;
        $typeApply = $itemSurvey->type_apply;
        if ($typeUser == 'staff') {
            $typeUser = 'staff';
            if ($typeApply == 'all_staff') {
                $listUser = $this->insertStaffAllApply($itemSurvey);
            } else {
                $listUser = $this->insertStaffApply($itemSurvey);
            }
        } else {
            $typeUser = 'customer';
            if ($typeApply == 'all_customer') {
                $listUser = $this->insertCustomerAllApply($itemSurvey);
            } else {
                $listUser = $this->insertCustomerApply($itemSurvey);
            }
        }

        return [
            'typeUser' => $typeUser,
            'listUser' => $listUser,
        ];
    }


    /**
     * xử lý insert áp dụng cho tất cả nhân viên khảo sát
     * @param $itemSurvey
     * @return void
     */

    public function insertStaffAllApply($itemSurvey)
    {
        $mStaff = new StaffsTable();
        $listStaff = $mStaff->getAll()->pluck('staff_id')->toArray();
        if (count($listStaff) > 0) {
            $itemSurvey->staffs()->sync($listStaff);
            return $listStaff;
        }
    }

    /**
     * xử lý insert áp dụng cho tất cả khách hàng khảo sát
     * @param $itemSurvey
     * @return void
     */

    public function insertCustomerAllApply($itemSurvey)
    {
        $mCustomer = new CustomerTable();
        $listCustomer = $mCustomer->getAll()->pluck('customer_id')->toArray();
        if (count($listCustomer) > 0) {
            $itemSurvey->customers()->sync($listCustomer);
            return $listCustomer;
        }
    }

    /**
     * xử lý insert áp dụng cho nhân viên khảo sát
     * @param $itemSurvey
     */

    public function insertStaffApply($itemSurvey)
    {
        $listStaffDefine = $itemSurvey->staffs()->pluck('staff_id')->toArray();
        $listStaffAuto = $this->getAllStaffAutoApply($itemSurvey);
        $listStaff  = array_unique(array_merge($listStaffAuto, $listStaffDefine));
        $itemSurvey->staffs()->sync($listStaff);
        return $listStaff;
    }

    /**
     * xử lý insert áp dụng khảo sát cho khách hàng
     * @param $itemSurvey
     */

    public function insertCustomerApply($itemSurvey)
    {
        $listCustomerDefine =  $itemSurvey->customers()->pluck('customer_id')->toArray();
        $listCustomerAuto = $this->getAllCustomerAutoApply($itemSurvey);
        $listCustomer  = array_unique(array_merge($listCustomerAuto, $listCustomerDefine));
        $itemSurvey->customers()->sync($listCustomer);
        return $listCustomer;
    }

    /**
     * xử lý lấy tất cả đối tượng nhân viên động 
     * @param $itemSurvey
     * @return array
     */

    public function getAllStaffAutoApply($itemSurvey)
    {
        $conditionApply = $itemSurvey->staffConditionApply;
        $listStaff = [];
        if ($conditionApply) {
            $mStaff = new StaffsTable();
            $filters['condition_branch'] =  $conditionApply->condition_branch ? json_decode($conditionApply->condition_branch) : null;
            $filters['condition_department'] = $conditionApply->condition_department ? json_decode($conditionApply->condition_department) : null;
            $filters['condition_titile'] = $conditionApply->condition_titile ? json_decode($conditionApply->condition_titile) : null;
            $type = $conditionApply->condition_type;
            $listStaffCondition = $mStaff->getAllByConditionAuto($filters, $type);

            if ($listStaffCondition->count() > 0) {
                $listStaff = $listStaffCondition->pluck('staff_id')->toArray();
            }
        }

        return $listStaff;
    }

    /**
     * xử lý tất cả đối tượng khách hàng động
     * @param $itemSurvey
     * @return array
     */

    public function getAllCustomerAutoApply($itemSurvey)
    {

        $conditionApply = $itemSurvey->conditionApply;
        $listCustomer = [];
        if ($conditionApply) {
            $mCustomerGroupFilter = new CustomerGroupFilterTable();
            $idGroup = $conditionApply->group_id;
            $itemCustomerGroup = $mCustomerGroupFilter->getItem($idGroup);
            if ($itemCustomerGroup != null) {
                $typeCustomerGroup = $itemCustomerGroup->filter_group_type;
                if ($typeCustomerGroup == 'user_define') {
                    $mCustomerGroupDefineDetail = new CustomerGroupDefineDetailTable();
                    $listCustomerGroup = $mCustomerGroupDefineDetail->getIdInGroup($idGroup);
                    if ($listCustomerGroup->count() > 0) {
                        $listCustomer = $listCustomerGroup->pluck('customer_id')->toArray();
                    }
                } else {
                    $rCustomerGroupFilter = app()->make(CustomerGroupFilterRepository::class);
                    $listCustomer = $rCustomerGroupFilter->getCustomerInGroupAuto($idGroup);
                }
            }
        }
        return $listCustomer;
    }

    /**
     * xử lý send notifi cho tất cả survey
     * @return void
     */
    public function handleNotifiApply($tenantId)
    {

        $listSurvey = $this->mSurvey->getListNotifiCondition();
        $listSurveyFiter = $this->filterDateSurvey($listSurvey);
        // chạy job khảo sát thời gian hiện tại và delay thời gian kết thúc
        if (count($listSurveyFiter['listSurveyDiff']) > 0) {
            $this->handleNotifiSurveyDiff($listSurveyFiter['listSurveyDiff'], $tenantId);
        }
        // chạy job khảo sát thời gian sau khi thời gian hiện tại 
        if (count($listSurveyFiter['listSurveyAfter']) > 0) {
            $this->handleNotifiSurveyAfter($listSurveyFiter['listSurveyAfter'], $tenantId);
        }
        // chạy job khảo sát ngay tức thì 
        if (count($listSurveyFiter['listSurveyNow']) > 0) {
            $this->handleNotifiSurveyNow($listSurveyFiter['listSurveyNow'], $tenantId);
        }
        // chạy job tự động 
        $this->handleNotifiAuTo($tenantId);
    }

    /**
     * lọc khảo sát theo thời gian tính từ lúc duyệt 
     * @param $listSurvey;
     * @return mixed
     */

    public function filterDateSurvey($listSurvey)
    {
        // danh sách khảo sát gửi thông báo lập tức 
        $listSurveyNow = [];
        // danh sách khảo sát gửi thông báo thời gian bắt đầu gửi thông báo là giờ hiện tại
        $listSurveyDiff = [];
        // danh sách khảo sát gửi thông báo 
        $listSurveyAfter = [];
        foreach ($listSurvey as $survey) {
            // kiểm tra nếu thời gian hiên tại mà lớn hơn thời gian kết thúc khảo sát thì bỏ qua
            if ($survey->end_date != null && Carbon::now()->greaterThan($survey->end_date)) {
                continue;
            }
            // kiểm tra thời gian gửi thông báo khảo sát không sát định
            else if ($survey->start_date == null) {
                $listSurveyNow[] = $survey;
            }
            // kiểm tra thời gian gửi thông báo khảo sát giới hạn 
            else if (Carbon::now()->between($survey->start_date, $survey->end_date)) {
                $listSurveyDiff[] = $survey;
            } else {
                $listSurveyAfter[] = $survey;
            }
        }
        return [
            'listSurveyDiff' => $listSurveyDiff,
            'listSurveyAfter' => $listSurveyAfter,
            'listSurveyNow'  => $listSurveyNow
        ];
    }

    /**
     * xử lí gửi thông báo ngay lập tức 
     * @param $listSurvey
     * @param $tenantId
     */

    public function handleNotifiSurveyNow($listSurvey, $tenantId)
    {
        foreach ($listSurvey as $survey) {
            $data = $this->getAllUserApply($survey);
            $listUser = $data['listUser'];
            $typeUser = $data['typeUser'];
            // cập nhật lại survey đã chạy job 
            $survey->update([
                'job_notifi' => 'R',
                'status_notifi' => 'R'
            ]);
            if (count($listUser) > 0) {
                $typeNotifi = 'survey_start';
                SaveNotificationSurvey::dispatch($listUser, $survey, $typeUser, $typeNotifi, $tenantId);
                // insert vào bảng thông báo 
                $dataInsert = [];
                foreach ($listUser as $item) {
                    $dataInsert[] = [
                        'user_id' => $item,
                        'survey_id' => $survey->survey_id
                    ];
                }
                $survey->notifiUser()->createMany($dataInsert);
            }
        }
    }

    /**
     * xử lí gửi thông báo thời gian bắt đầu ngay lập tức và delay thời gian kêt thúc
     * @param $listSurvey
     * @param $tenantId
     * @return void
     */
    public function handleNotifiSurveyDiff($listSurvey, $tenantId)
    {
        foreach ($listSurvey as $survey) {
            $delayEnd = Carbon::parse($survey->end_date)->subHours(2);
            $data = $this->getAllUserApply($survey);
            $listUser = $data['listUser'];
            $typeUser = $data['typeUser'];
            // cập nhật lại survey đã chạy job 
            $survey->update([
                'job_notifi' => 'R',
                'status_notifi' => 'R'
            ]);
            if (count($listUser) > 0) {
                $typeNotifiStart = 'survey_start';
                $typeNotifiEnd = 'survey_end';
                SaveNotificationSurvey::dispatch(
                    $listUser,
                    $survey,
                    $typeUser,
                    $typeNotifiStart,
                    $tenantId
                );
                SaveNotificationSurvey::dispatch(
                    $listUser,
                    $survey,
                    $typeUser,
                    $typeNotifiEnd,
                    $tenantId
                )->delay($delayEnd);
                // insert vào bảng thông báo 
                $dataInsert = [];
                foreach ($listUser as $item) {
                    $dataInsert[] = [
                        'user_id' => $item,
                        'survey_id' => $survey->survey_id
                    ];
                }
                $survey->notifiUser()->createMany($dataInsert);
            }
        }
    }

    /**
     * xử lí gửi thông báo thời gian bắt đầu deley và thời gian kết thức delay
     * @param $listSurvey
     * @param $tenantId
     * @return void
     */
    public function handleNotifiSurveyAfter($listSurvey, $tenantId)
    {
        foreach ($listSurvey as $survey) {
            $delayStart = Carbon::parse($survey->start_date);
            $delayEnd = Carbon::parse($survey->end_date)->subHours(2);
            $data = $this->getAllUserApply($survey);
            $listUser = $data['listUser'];
            $typeUser = $data['typeUser'];
            // cập nhật lại survey đã chạy job 
            $survey->update([
                'job_notifi' => 'R',
                'status_notifi' => 'R'
            ]);
            if (count($listUser) > 0) {
                $typeNotifiStart = 'survey_start';
                $typeNotifiEnd = 'survey_end';
                SaveNotificationSurvey::dispatch(
                    $listUser,
                    $survey,
                    $typeUser,
                    $typeNotifiStart,
                    $tenantId
                )->delay($delayStart);
                SaveNotificationSurvey::dispatch(
                    $listUser,
                    $survey,
                    $typeUser,
                    $typeNotifiEnd,
                    $tenantId
                )->delay($delayEnd);
                // insert vào bảng thông báo 
                $dataInsert = [];
                foreach ($listUser as $item) {
                    $dataInsert[] = [
                        'user_id' => $item,
                        'survey_id' => $survey->survey_id
                    ];
                }
                $survey->notifiUser()->createMany($dataInsert);
            }
        }
    }

    /**
     * xử lí thông báo thời gian chạy tự động auto
     * @param $tenantId
     */

    public function handleNotifiAuTo($tenantId)
    {
        $filters['auto'] = 1;
        $listSurvey = $this->mSurvey->getListNotifiCondition($filters);
        if ($listSurvey->count() > 0) {
            foreach ($listSurvey as $survey) {
                if ($survey->type_user == 'staff') {
                    $this->hanldeNotiAutoStaff($survey, $tenantId);
                } else {

                    $this->hanldeNotiAutoCustomer($survey, $tenantId);
                }
            }
        }
    }

    /**
     * cập nhật thông báo cho nhân viên tự động
     * @param $itemSurvey
     * @return void
     */

    public function hanldeNotiAutoStaff($itemSurvey, $tenantId)
    {

        // check điều kiện all tất cả staff //
        if ($itemSurvey->type_apply == 'all_staff') {
            $listStaffDefine = $itemSurvey->staffs()->pluck('staff_id')->toArray();
            $mStaff  = app()->get(StaffsTable::class);
            $listStaff = $mStaff->getAll()->pluck('staff_id')->toArray();
            $diffStaff  = [];
            foreach ($listStaff as $item) {
                if (!in_array($item, $listStaffDefine)) {
                    $diffStaff[] = $item;
                }
            }
            if (count($diffStaff) > 0) {
                // chạy job trong này 
                $typeNotifi = 'survey_start';
                SaveNotificationSurvey::dispatch(
                    $diffStaff,
                    $itemSurvey,
                    'staff',
                    $typeNotifi,
                    $tenantId
                );
                // update logStaff //
                foreach ($diffStaff as $item) {
                    $itemSurvey->notifiUser()->create(['user_id' => $item]);
                }
            }
            $listStaff  = array_unique(array_merge($listStaffDefine, $listStaff));
            $itemSurvey->staffs()->sync($listStaff);
        } else {
            $listStaffDefine = $itemSurvey->staffs()->pluck('staff_id')->toArray();
            $listStaffAuto = $this->getAllStaffAutoApply($itemSurvey);
            $listStaff  = array_unique(array_merge($listStaffAuto, $listStaffDefine));
            $diffStaff  = [];
            $listLogStaff = $itemSurvey->notifiUser();
            if ($listLogStaff->count() > 0) {
                $listLogStaff = $listLogStaff->pluck('user_id')->toArray();
            } else {
                $listLogStaff = [];
            }
            foreach ($listStaff as $item) {
                if (!in_array($item, $listLogStaff)) {
                    $diffStaff[] = $item;
                }
            }
            if (count($diffStaff) > 0) {
                // chạy job trong này 
                $typeNotifi = 'survey_start';
                SaveNotificationSurvey::dispatch(
                    $diffStaff,
                    $itemSurvey,
                    'staff',
                    $typeNotifi,
                    $tenantId
                );
                // update logStaff //
                foreach ($diffStaff as $item) {
                    $itemSurvey->notifiUser()->create(['user_id' => $item]);
                }
            }
            $itemSurvey->staffs()->sync($listStaff);
        }
    }

    /**
     * cập nhật thông báo cho khách hàng tự động
     * @param $itemSurvey
     * @return void
     */

    public function hanldeNotiAutoCustomer($itemSurvey, $tenantId)
    {
        if ($itemSurvey->type_apply == 'all_customer') {
            $listCustomerDefine =  $itemSurvey->customers()->pluck('customer_id')->toArray();
            $mCustomer = app()->get(CustomerTable::class);
            $listCustomer = $mCustomer->getAll()->pluck('customer_id')->toArray();
            $diffCustomer  = [];
            foreach ($listCustomer as $item) {
                if (!in_array($item, $listCustomerDefine)) {
                    $diffCustomer[] = $item;
                }
            }
            if (count($diffCustomer) > 0) {
                // chạy job trong này
                $typeNotifi = 'survey_start';
                SaveNotificationSurvey::dispatch(
                    $diffCustomer,
                    $itemSurvey,
                    'customer',
                    $typeNotifi,
                    $tenantId
                );

                // update logStaff //
                foreach ($diffCustomer as $item) {
                    $itemSurvey->notifiUser()->create(['user_id' => $item]);
                }
            }
            $listCustomerNew  = array_unique(array_merge($listCustomer, $listCustomerDefine));
            $itemSurvey->customers()->sync($listCustomerNew);
        } else {
            $listCustomerDefine =  $itemSurvey->customers()->pluck('customer_id')->toArray();
            $listCustomerAuto = $this->getAllCustomerAutoApply($itemSurvey);
            $listCustomer  = array_unique(array_merge($listCustomerAuto, $listCustomerDefine));
            $diffCustomer = [];
            $listLogCustomer = $itemSurvey->notifiUser();
            if ($listLogCustomer->count() > 0) {
                $listLogCustomer = $listLogCustomer->pluck('user_id')->toArray();
            } else {
                $listLogCustomer = [];
            }
            foreach ($listCustomer as $item) {
                if (!in_array($item, $listLogCustomer)) {
                    $diffCustomer[] = $item;
                }
            }
            if (count($diffCustomer) > 0) {
                // chạy job trong này
                $typeNotifi = 'survey_start';
                SaveNotificationSurvey::dispatch(
                    $diffCustomer,
                    $itemSurvey,
                    'customer',
                    $typeNotifi,
                    $tenantId
                );

                // update logStaff //
                foreach ($diffCustomer as $item) {
                    $itemSurvey->notifiUser()->create(['user_id' => $item]);
                }
            }
            $itemSurvey->customers()->sync($listCustomer);
        }
    }

    /**
     * Xử lý gửi thông báo khảo sát tính điểm 
     * @param $tenantId
     */

    public function handleNotifiAfterSurveyCountPoint($tenantId)
    {
        /**
         *  Lấy trường gửi thông báo sau khi kết thúc chương trình khảo sát
         *  Lấy danh sách phiên trả lời có khảo sát tính điểm và có chọn ngày kết thúc và có cấu hình là E và có user là nhân viên (done)
         *  Tạo mẫu thông báo 
         *  Gửi thông báo 
         */
        // danh sách hoàn thành phiên khảo sát tính điểm thông báo sau khi kết thúc khảo sát
        try {
            // Gửi thông báo khi cấu hình tính điểm gửi thông báo sau khi khảo sát kết thúc
            $this->sendNotifiWhenEndSurveyPoint($tenantId);
            // Gửi thông báo khi cấu hình tính điểm thông báo khoảng thời gian
            $this->sendNotifiPointDateBetween($tenantId);
        } catch (\Exception $ex) {
            Log::info("error" . $ex->getMessage());
        }
    }

    /**
     * Lấy danh sách phiên trả lời có khảo sát tính điểm và có chọn ngày kết thúc và có cấu hình là E và có user là nhân viên
     * @param $condition
     * @return mixed
     */
    public function getSurveyConpointCondition($condition)
    {
        $mSurveyAnswer = app()->get(SurveyAnswerTable::class);
        $surveyItem = $mSurveyAnswer->getSessionAnswerCountPointCondition($condition);
        return $surveyItem;
    }

    /**
     * Xử lý Gửi thông báo khi cấu hình tính điểm gửi thông báo sau khi khảo sát kết thúc
     * @param $tenantId
     * @return mixed
     */

    public function sendNotifiWhenEndSurveyPoint($tenantId)
    {
        $notifiEndConfigPoint = SurveyConfigPointTable::SHOW_ANSWER_END;
        $listSurvey = $this->getSurveyConpointCondition($notifiEndConfigPoint);
        if ($listSurvey->count() > 0) {
            foreach ($listSurvey as $item) {
                // setjob và gửi noti đi //
                SendNotificationPointSurvey::dispatch($item, $tenantId);
            }
        }
    }

    /**
     * Xử lý gửi thông báo khi cấu hình tính điểm thông báo khoảng thời gian
     * @param $tenantId
     * @return mixed
     */

    public function sendNotifiPointDateBetween($tenantId)
    {
        $notifiEndConfigPoint = SurveyConfigPointTable::SHOW_ANSWER_BETWEEN;
        $listSurvey = $this->getSurveyConpointCondition($notifiEndConfigPoint);
        if ($listSurvey->count() > 0) {
            foreach ($listSurvey as $item) {
                // setjob và gửi noti đi //
                SendNotificationPointSurvey::dispatch($item, $tenantId);
            }
        }
    }
}
