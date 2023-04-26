<?php

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class SurveyAnswerQuestionTable extends Model
{
    use ListTableTrait;

    protected $connection = BRAND_CONNECTION;
    protected $table = "survey_answer_question";
    protected $primaryKey = "answer_question_id";
    protected $fillable = [
        "answer_question_id",
        "survey_id",
        "survey_answer_id",
        "branch_id",
        "survey_question_id",
        "survey_question_choice_id",
        "answer_value",
        "created_at",
        "updated_at",
    ];


    public function getBySurveyQuestion($id)
    {
        $select = $this->select(
            "{$this->table}.survey_question_id",
            "{$this->table}.survey_question_choice_id",
            "{$this->table}.answer_value",
            "survey_question_choice.survey_question_choice_title",
            \DB::raw('CONCAT(dmspro_mys_outlet_master.customer_code, "||",
                dmspro_mys_outlet_master.ship_to_code, 
                "||", dmspro_mys_survey_answer_question.survey_answer_id,
                "||", dmspro_mys_survey_answer_question.survey_question_id) AS customer_ship_code_sai_sqi')
        )
            ->join("outlet_master", "outlet_master.outlet_id", "{$this->table}.outlet_id")
            ->leftjoin(
                "survey_question_choice",
                "survey_question_choice.survey_question_choice_id",
                "{$this->table}.survey_question_choice_id"
            );
        if (is_array($id)) {
            $select->whereIn("{$this->table}.survey_question_id", $id);
        } else {
            $select->where("{$this->table}.survey_question_id", $id);
        }
        $select->groupBy(
            //            "{$this->table}.survey_question_id"
            //            "{$this->table}.outlet_id",
            //            "{$this->table}.survey_question_choice_id"
        );
        return $select->get();
    }

    public function geByCondition($condition = [])
    {
        $select = $this
            ->select(
                "{$this->table}.survey_question_id",
                "{$this->table}.survey_question_choice_id",
                "{$this->table}.answer_value",
                "survey_question_choice.survey_question_choice_title",
                \DB::raw('CONCAT(branches.representative_code, "||",
                branches.branch_code,
                "||", survey_answer_question.survey_answer_id,
                "||", survey_answer_question.survey_question_id) AS customer_ship_code_sai_sqi')
            )
            ->join("branches", "branches.branch_id", "{$this->table}.branch_id")
            ->leftjoin(
                "survey_question_choice",
                "survey_question_choice.survey_question_choice_id",
                "{$this->table}.survey_question_choice_id"
            );
        if (isset($condition['survey_question$survey_id'])) {
            $select->where("{$this->table}.survey_id", $condition['survey_question$survey_id']);
        }
        if (isset($condition['where_in_outlet_id'])) {
            $select->whereIn("{$this->table}.outlet_id", $condition['where_in_outlet_id']);
        }
        if (isset($condition['where_in_survey_question_id'])) {
            $select->whereIn("{$this->table}.survey_question_id", $condition['where_in_survey_question_id']);
        }
        return $select->get();
    }



    public function getListCore(&$filters = [])
    {
        $select = $this->select(
            "{$this->table}.*",
            "survey_answer.branch_id",
            "survey_answer.user_id",
            "survey_answer.user_type",
            "survey_answer.survey_id",
            'survey_answer.survey_answer_id',
            "survey_answer.survey_answer_status",
            "survey_answer.total_questions",
            "survey_answer.num_questions_completed",
            "survey_answer.accumulation_point",
            "survey_answer.finished_at",
            "staffs.staff_id",
            "customers.customer_id",
            'survey_answer.survey_answer_id',
            "survey_answer.created_at as create_at_survey",
            \DB::raw("IF(staffs.full_name = NULL ,customers.full_name , staffs.full_name) as full_name"),
            \DB::raw("IF(staffs.staff_id = NULL ,customers.customer_id , staffs.staff_id) as id_user"),
            \DB::raw("IF(staffs.staff_code = NULL ,customers.customer_code , staffs.staff_code) as code_user"),
            \DB::raw("IF(staffs.phone1 = NULL ,customers.phone1 , staffs.phone1) as phone"),
            \DB::raw("IF(staffs.address = NULL ,customers.address , staffs.address) as address"),

        );
        if (isset($filters['idSurvey'])) {
            $select->where("{$this->table}.survey_id", $filters['idSurvey']);
        }
        $select->join("survey_answer", "survey_answer.survey_answer_id", "{$this->table}.survey_answer_id")
            ->leftJoin('staffs', function ($join) {
                $join->on('survey_answer.user_id', '=', 'staffs.staff_id');
                $join->where("survey_answer.user_type", '=', 'staff');
            })
            ->leftJoin('customers', function ($join) {
                $join->on('survey_answer.user_id', '=', 'customers.customer_id');
                $join->where("survey_answer.user_type", '=', 'customer');
            });
        if (isset($filters['nameCustomerOrStaff'])) {
            $searchCode = $filters['nameCustomerOrStaff'];
            $select->where(function ($query) use ($searchCode) {
                $query->where("staffs.full_name", "LIKE", "%" . $searchCode . "%")
                    ->orWhere("customers.full_name", "LIKE", "%" . $searchCode . "%");
            });
            unset($filters['nameCustomerOrStaff']);
        }

        if (isset($filters['codeCustomerOrStaff'])) {
            $searchName = $filters['codeCustomerOrStaff'];
            $select->where(function ($query) use ($searchName) {
                $query->where("staffs.staff_code", "LIKE", "%" . $searchName . "%")
                    ->orWhere("customers.customer_code", "LIKE", "%" . $searchName . "%");
            });
            unset($filters['codeCustomerOrStaff']);
        }
        if (isset($filters['dateCreated'])) {
            $arrFilter = explode(" - ", $filters["dateCreated"]);
            $startTime = Carbon::createFromFormat('d/m/Y', $arrFilter[0])->format('Y-m-d');
            $endTime = Carbon::createFromFormat('d/m/Y', $arrFilter[1])->format('Y-m-d');
            $select->whereBetween("survey_answer.created_at", [$startTime . ' 00:00:00', $endTime . ' 23:59:59']);
            unset($filters['dateCreated']);
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
        $select->groupBy("survey_answer.survey_answer_id");
        $select->orderBy("survey_answer.survey_answer_id", 'DESC');
        $page = (int)($filters['page'] ?? 1);
        $display = (int)($filters['perpage'] ?? PAGING_ITEM_PER_PAGE);
        unset($filters['perpage']);
        unset($filters['page']);
        return $select->paginate($display, $columns = ['*'], $pageName = 'page', $page);
    }

    /**
     * lấy danh sách câu trả lời và câu hỏi theo id phiên trả lời  
     * @param $id_answer
     * @return mixed
     */

    public function getAllByIdAnswer($id_answer)
    {
        $select = $this->select(
            "{$this->table}.survey_answer_id",
            "{$this->table}.survey_question_choice_id",
            "{$this->table}.answer_value",
            "survey_block.survey_block_name",
            "survey_block.survey_block_position",
            "survey_question.survey_question_id",
            "survey_question.survey_block_id",
            "survey_question.survey_question_title",
            "survey_question.survey_question_description",
            "survey_question.survey_question_type",
            "survey_question.survey_question_config",
            "survey_question.survey_question_position",
        )
            ->where("{$this->table}.survey_answer_id", $id_answer)
            ->join("survey_question", "survey_question.survey_question_id", "{$this->table}.survey_question_id")
            ->join("survey_block", function ($join) {
                $join->on("survey_block.survey_id", "=", "{$this->table}.survey_id");
                $join->on("survey_block.survey_block_id", "=", "survey_question.survey_block_id");
            })
            ->orderBy('survey_block_position')
            ->orderBy('survey_question_position')
            ->get();
        return $select;
    }
}
