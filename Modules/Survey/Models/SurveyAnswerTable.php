<?php

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Survey\Models\SurveyTable;
use Illuminate\Database\Eloquent\Model;
use MyCore\Models\Traits\ListTableTrait;
use Modules\Survey\Models\SurveyConfigPointTable;

class SurveyAnswerTable extends Model
{
    // use ListTableTrait;

    protected $connection = BRAND_CONNECTION;
    protected $table = "survey_answer";
    protected $primaryKey = "survey_answer_id";
    const IS_NOT_NOTIFI = NULL;
    const IS_NOTIFI = 1;
    const STATUS_PROCESS = 'done';
    /**
     * Danh sách câu hỏi
     * @param array $filter
     * @return mixed
     */
    public function getListCore(&$filter = [])
    {
        $select = $this->select(
            "{$this->table}.survey_answer_id",
            "{$this->table}.branch_id",
            "{$this->table}.survey_answer_status",
            "{$this->table}.total_questions",
            "{$this->table}.num_questions_completed",
            "{$this->table}.accumulation_point",
            "{$this->table}.finished_at",
            "branches.branch_code",
            "branches.representative_code",
            "branches.branch_name",
            \DB::raw('CONCAT(branches.branch_code, "||",
            branches.representative_code,
                "||", survey_answer.survey_answer_id) AS customer_ship_code_sai'),
            \DB::raw('CONCAT(branches.branch_code, "||",
            branches.representative_code) AS customer_ship_code')
        )
            ->join("branches", "branches.branch_id", "{$this->table}.branch_id");
        if (isset($filter['keyword_outlet'])) {
            $select->where(function ($query) use ($filter) {
                $query
                    ->where("branches.branch_code", "LIKE", "%" . $filter['keyword_outlet'] . "%")
                    ->orWhere("branches.representative_code", "LIKE", "%" . $filter['keyword_outlet'] . "%")
                    ->orWhere("branches.branch_name", "LIKE", "%" . $filter['keyword_outlet'] . "%");
            });
            unset($filter['keyword_outlet']);
        }
        if (isset($filter['finished_at_start'])) {
            $select->whereDate("{$this->table}.finished_at", ">=", $filter['finished_at_start']);
            unset($filter['finished_at_start']);
        }
        if (isset($filter['finished_at_end'])) {
            $select->whereDate("{$this->table}.finished_at", "<=", $filter['finished_at_end']);
            unset($filter['finished_at_end']);
        }
        $select->groupBy("{$this->table}.survey_answer_id")
            ->orderBy("{$this->table}.finished_at", 'DESC');
        return $select;
    }

    public function getListCoreNews(&$filters = [])
    {
        $typeUser = $filters['typeUser'] ?? 'staff';
        if ($typeUser == 'customer') {
            $select = $this->select(
                "{$this->table}.*",
                "{$this->table}.branch_id",
                "{$this->table}.user_id",
                "{$this->table}.survey_id",
                "{$this->table}.survey_answer_id",
                "{$this->table}.survey_answer_status",
                "{$this->table}.total_questions",
                "{$this->table}.num_questions_completed",
                "{$this->table}.accumulation_point",
                "{$this->table}.finished_at",
                "{$this->table}.survey_answer_id",
                "{$this->table}.created_at as create_at_survey",
                "customers.customer_code as code",
                "customers.full_name",
                "customers.customer_id as id_user",
                "customers.phone1 as phone",
                "customers.address"
            );
            if (isset($filters['idSurvey'])) {
                $select->where("{$this->table}.survey_id", $filters['idSurvey']);
            }

            $select
                ->leftJoin('customers', function ($join) {
                    $join->on("{$this->table}.user_id", '=', 'customers.customer_id');
                });
            if (isset($filters['nameCustomerOrStaff'])) {
                $searchCode = $filters['nameCustomerOrStaff'];
                $select->where("customers.full_name", "LIKE", "%" . $searchCode . "%");
                unset($filters['nameCustomerOrStaff']);
            }

            if (isset($filters['codeCustomerOrStaff'])) {
                $searchCode = $filters['codeCustomerOrStaff'];
                $select->where("customers.customer_code", $searchCode);
                unset($filters['codeCustomerOrStaff']);
            }

            if (isset($filters['province'])) {
                $searchProvince = $filters['province'];
                $select->where("customers.province_id", $searchProvince);
                unset($filters['province']);
            }
            if (isset($filters['district'])) {
                $searchDistric = $filters['district'];
                $select->where("customers.district_id", $searchDistric);
                unset($filters['district']);
            }
            if (isset($filters['ward'])) {
                $searchDistric = $filters['ward'];
                $select->where("customers.ward_id", $searchDistric);
                unset($filters['ward']);
            }
        } else {
            $select = $this->select(
                "{$this->table}.*",
                "{$this->table}.branch_id",
                "{$this->table}.user_id",
                "{$this->table}.survey_id",
                "{$this->table}.survey_answer_id",
                "{$this->table}.survey_answer_status",
                "{$this->table}.total_questions",
                "{$this->table}.num_questions_completed",
                "{$this->table}.accumulation_point",
                "{$this->table}.finished_at",
                "{$this->table}.survey_answer_id",
                "{$this->table}.created_at as create_at_survey",
                "staffs.full_name",
                "staffs.staff_id as id_user ",
                "staffs.phone1 as phone",
                "staffs.address",
                "staffs.staff_code as code",
            );
            if (isset($filters['idSurvey'])) {
                $select->where("{$this->table}.survey_id", $filters['idSurvey']);
            }

            $select
                ->leftJoin('staffs', function ($join) {
                    $join->on("{$this->table}.user_id", '=', 'staffs.staff_id');
                });
            if (isset($filters['nameCustomerOrStaff'])) {
                $searchCode = $filters['nameCustomerOrStaff'];
                $select->where("staffs.full_name", "LIKE", "%" . $searchCode . "%");
                unset($filters['nameCustomerOrStaff']);
            }
            if (isset($filters['codeCustomerOrStaff'])) {
                $searchCode = $filters['codeCustomerOrStaff'];
                $select->where("staffs.staff_code", $searchCode);
                unset($filters['codeCustomerOrStaff']);
            }
        }
        if (isset($filters['dateCreated'])) {
            $arrFilter = explode(" - ", $filters["dateCreated"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arrFilter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arrFilter[1])->format('Y-m-d');
            $select->whereBetween("{$this->table}.created_at", [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
            unset($filters['dateCreated']);
        }
        $select->orderBy("{$this->table}.survey_answer_id", 'DESC');
        $page = (int)($filters['page'] ?? 1);
        $display = (int)($filters['perpage'] ?? PAGING_ITEM_PER_PAGE);
        unset($filters['perpage']);
        unset($filters['page']);
        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    /**
     * lấy danh sách id trả lời khảo sat user 
     * @param $filters
     * @return mixed
     */
    public function getListAnswerBySurvey(&$filters = [])
    {
        $typeUser = $filters['typeUser'] ?? 'staff';
        $select = $this->select(
            "{$this->table}.survey_answer_id",
            "{$this->table}.total_questions",
            "{$this->table}.num_questions_completed",
            "{$this->table}.created_at",
            "staffs.full_name",
            "staffs.staff_id as id_user ",
            "staffs.phone1 as phone",
            "staffs.address",
            "staffs.staff_code as code"
        );
        if ($typeUser == 'customer') {
            $select = $this->select(
                "{$this->table}.survey_answer_id",
                "{$this->table}.total_questions",
                "{$this->table}.num_questions_completed",
                "{$this->table}.created_at",
                "customers.customer_code as code",
                "customers.full_name",
                "customers.customer_id as id_user",
                "customers.phone1 as phone",
                "customers.address"
            );
        }
        $select->where("{$this->table}.survey_id", $filters['survey_id']);
        if ($typeUser == 'customer') {
            $select->leftJoin('customers', function ($join) {
                $join->on("{$this->table}.user_id", '=', 'customers.customer_id');
            });
        } else {
            $select->leftJoin('staffs', function ($join) {
                $join->on("{$this->table}.user_id", '=', 'staffs.staff_id');
            });
        }
        $select->orderBy("{$this->table}.survey_answer_id", 'DESC');
        $page = (int)($filters['page'] ?? 1);
        $display = (int)($filters['perpage'] ?? 1);
        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }


    /**
     * lấy danh sách id câu trả lời và select cột theo bảng excel 
     * @param $filters
     * @return mixed
     */
    public function getAnswerQuestionExportExcel(&$filters = [])
    {
        $typeUser = $filters['typeUser'] ?? 'staff';
        $select = $this->select(
            "{$this->table}.survey_answer_id",
            "staffs.staff_code as code",
            "staffs.full_name",
            "staffs.phone1 as phone",
            "{$this->table}.created_at",
            "staffs.address"
        );
        if ($typeUser == 'customer') {
            $select = $this->select(
                "{$this->table}.survey_answer_id",
                "customers.customer_code as code",
                "customers.full_name",
                "customers.phone1 as phone",
                "customers.address",
                "{$this->table}.created_at"
            );
        }
        $select->where("{$this->table}.survey_id", $filters['survey_id']);
        if ($typeUser == 'customer') {
            $select->leftJoin('customers', function ($join) {
                $join->on("{$this->table}.user_id", '=', 'customers.customer_id');
            });
        } else {
            $select->leftJoin('staffs', function ($join) {
                $join->on("{$this->table}.user_id", '=', 'staffs.staff_id');
            });
        }
        $select->orderBy("{$this->table}.survey_answer_id", 'DESC');
        return $select->get();
    }

    /**
     * hàm lấy các phiên trả lời user mới nhất của khảo sát 
     */

    public function getAnswerNewUser($id_survey)
    {
        $select = $this->selectRaw("
        user_id,
        MAX(survey_answer_id) as survey_answer_id");
        $select->where("survey_id", $id_survey)
            ->groupBy("user_id");
        return $select->get();
    }

    /**
     * Lấy danh sách phiên trả lời theo điều kiện tính điểm
     * @param $condition
     * @return mixed
     */

    public function getSessionAnswerCountPointCondition($condition)
    {

        $oSelect = $this
            ->select(
                "{$this->table}.*",
                "sv.survey_name"
            )
            ->where("is_notifi", self::IS_NOT_NOTIFI)
            ->where("survey_answer_status", self::STATUS_PROCESS)
            ->join("survey as sv", function ($join) use ($condition) {
                $join->on("sv.survey_id", "{$this->table}.survey_id")
                    ->where("sv.count_point", SurveyTable::COUNT_POINT)
                    ->where("sv.type_user", SurveyTable::TYPE_APPY_STAFF);
                if ($condition == SurveyConfigPointTable::SHOW_ANSWER_END) {
                    $join->where("sv.is_exec_time", SurveyTable::IS_EXEC_TIME);
                    $join->whereDate("sv.end_date", "<=", date('Y-m-d H:i'));
                }
            })
            ->join("survey_config_point as scp", function ($j) use ($condition) {
                $j->on("scp.survey_id", "{$this->table}.survey_id");
                if ($condition == SurveyConfigPointTable::SHOW_ANSWER_END) {
                    $j->where("scp.show_answer", SurveyConfigPointTable::SHOW_ANSWER_END);
                } else if ($condition == SurveyConfigPointTable::SHOW_ANSWER_BETWEEN) {
                    $j->where("scp.show_answer", SurveyConfigPointTable::SHOW_ANSWER_BETWEEN);
                    $now = Carbon::now()->format('Y-m-d H:i:s');
                    $j->where(function ($query) use ($now) {
                        $query->where("scp.time_start", "<=", $now)
                            ->orWhere("scp.time_end", "<=", $now);
                    });
                }
            })
            ->join("staffs as s", "s.staff_id", "{$this->table}.user_id")
            ->get();
        return $oSelect;
    }
}
