<?php

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Modules\Survey\Models\StaffsTable;
use Illuminate\Database\Eloquent\Model;
use Modules\Survey\Models\CustomerTable;
use MyCore\Models\Traits\ListTableTrait;
use Modules\Survey\Models\StaffGroupTable;
use Modules\Survey\Models\SurveyQuestionTable;
use Modules\Survey\Models\SurveyAnswerQuestionTable;
use Modules\Survey\Models\SurveyConditionApplyTable;
use Modules\Survey\Models\SurveyUserNotificationTable;

class SurveyTable extends Model
{
    protected $connection = BRAND_CONNECTION;
    protected $table = 'survey';
    protected $primaryKey = 'survey_id';
    protected $fillable = [
        'survey_id', 'survey_name', 'survey_code',
        'survey_description', 'survey_banner',
        'is_exec_time', 'start_date', 'end_date', 'close_date',
        'frequency', 'frequency_value', 'is_limit_exec_time',
        'exec_time_from', 'exec_time_to',
        'frequency_monthly_type', 'day_in_monthly',
        'day_in_week', 'day_in_week_repeat',
        'period_in_date_type', 'period_in_date_start',
        'period_in_date_end', 'max_times', 'branch_max_times_per_day',
        'branch_max_times', 'allow_all_branch',
        'is_active', 'is_delete', 'created_at',
        'created_by', 'updated_at', 'updated_by', 'status', 'type_user', 'type_apply',
        'status_notifi', 'job_notifi',
        'count_point', 'is_short_link', 'short_link'
    ];
    const COUNT_POINT = 1;
    const IS_EXEC_TIME = 1;
    const STATUS_OPEN = 'N';
    const TYPE_APPY_STAFF = 'staff';
    /**
     * Danh sách khảo sát.
     * @param array $filters
     * @return mixed
     */
    public function getListCore(&$filters = [])
    {
        $select = $this->select("{$this->table}.*");
        if (isset($filters['nameOrCodeSurvey'])) {
            $select->where(function ($query) use ($filters) {
                $query->where("survey_name", 'like', '%' . $filters['nameOrCodeSurvey'] . '%')
                    ->orWhere("survey_code", 'like', '%' . $filters['nameOrCodeSurvey'] . '%');
            });
            unset($filters['nameOrCodeSurvey']);
        }
        if (isset($filters['dateCreated'])) {
            $arrFilter = explode(" - ", $filters["dateCreated"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arrFilter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arrFilter[1])->format('Y-m-d');
            $select->whereBetween("{$this->table}.created_at", [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
            unset($filters['dateCreated']);
        }
        if (isset($filters['status'])) {
            $select->where("{$this->table}.status", $filters['status']);
            unset($filters['status']);
        }
        $select
            ->where("{$this->table}.is_delete", 0)
            ->orderBy("{$this->table}.{$this->primaryKey}", "DESC");
        return $select;
    }

    /**
     * Lấy tất cả danh sách khảo sát
     * @param array $filters 
     */

    public function getListNotifiCondition($filters = [])
    {
        $select = $this->where("is_delete", 0)
            ->where('status', 'R')
            ->where("type_user", "staff");
        if (isset($filters['auto'])) {
            $select->where('job_notifi', 'R')
                ->where('status_notifi', 'R');
        } else {
            $select->where('status_notifi', null);
        }
        return $select->get();
    }





    /**
     * Add one record
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        $select = $this->create($data);
        return $select->{$this->primaryKey};
    }

    /**
     * Edit by survey_id
     * @param $id
     * @param $data
     * @return mixed
     */
    public function edit($id, $data)
    {
        $select = $this->where("{$this->table}.survey_id", $id)->update($data);
        return $select;
    }

    /**
     * Get by survey_id
     * @param $id
     * @return mixed
     */
    public function getItem($id)
    {
        $select = $this->select("{$this->table}.*")
            ->where("{$this->table}.survey_id", $id)
            ->where("{$this->table}.is_delete", 0)
            ->first();
        return $select;
    }

    /**
     * Khi ngày hiện tại > ngày kết thúc thì đóng khảo sát lại
     */
    public function closeSurvey()
    {
        $this->where('is_exec_time', 1)
            ->where('end_date', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->update([
                'status' => 'C',
                'updated_at' => Carbon::now()
            ]);
    }

    /**
     * lấy các khối block của survey
     * @param $idSurrvey
     * @return mixed
     */

    public function getBlockSurvey($idSurrvey)
    {
        $select = $this->select(
            "{$this->table}.*",
            "survey_block.survey_block_id as block_id",
            "survey_block.survey_block_position"
        )
            ->where("{$this->table}.survey_id", $idSurrvey)
            ->where("{$this->table}.is_delete", 0)
            ->join('survey_block', "survey_block.survey_id", "{$this->table}.survey_id")
            ->orderBy('survey_block.survey_block_position')
            ->get();
        return $select;
    }
    /**
     * quan hệ một nhiều với bảng câu hỏi
     */
    public function questions()
    {
        return $this->hasMany(SurveyQuestionTable::class, 'survey_id');
    }
    /**
     * quan hệ một nhiều với bảng câu hỏi và cau trả lời 
     */
    public function answerQuestion()
    {
        return $this->hasManyThrough(
            SurveyAnswerQuestionTable::class,
            SurveyQuestionTable::class,
            'survey_id',
            'survey_question_id'
        );
    }
    /**
     * quan hệ một nhiều với bảng staffs
     */
    public function staffs()
    {
        return $this->belongsToMany(
            StaffsTable::class,
            'survey_apply_user',
            'survey_id',
            'user_id'
        );
    }

    /**
     * quan hệ một nhiều với bảng staffs
     */
    public function customers()
    {
        return $this->belongsToMany(
            CustomerTable::class,
            'survey_apply_user',
            'survey_id',
            'user_id'
        );
    }

    /**
     * quan hệ một một với bảng survey_condition_apply
     */
    public function conditionApply()
    {
        return $this->hasOne(
            SurveyConditionApplyTable::class,
            'survey_id'
        );
    }

    /**
     * quan hê một một với bảng survey_group_staffs
     */

    public function staffConditionApply()
    {
        return $this->hasOne(
            StaffGroupTable::class,
            'survey_id'
        );
    }

    /**
     * quan hệ một nhiều với bảng survey_user_notification
     */

    public function notifiUser()
    {
        return $this->hasMany(
            SurveyUserNotificationTable::class,
            'survey_id'
        );
    }
}
