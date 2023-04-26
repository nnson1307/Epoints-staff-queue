<?php

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MyCore\Models\Traits\ListTableTrait;
use Modules\Survey\Models\SurveyQuestionChoiceTable;

class SurveyQuestionTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    protected $table = 'survey_question';
    protected $primaryKey = 'survey_question_id';
    protected $fillable = [
        'survey_question_id',
        'parent_id',
        'survey_id',
        'survey_block_id',
        'survey_question_title',
        'survey_question_description',
        'survey_question_type',
        'survey_question_config',
        'is_required',
        'is_combine_question',
        'survey_question_position',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * Danh sách câu hỏi
     * @param array $filter
     * @return mixed
     */
    public function getListCore(&$filter = [])
    {
        $select = $this->select("{$this->table}.*");
        if (isset($filter['where_not_in_survey_question_type'])) {
            $select->whereNotIn("{$this->table}.survey_question_type", $filter['where_not_in_survey_question_type']);
        }
        return $select;
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
     * Get by survey_id
     * @param $id
     * @return mixed
     */
    public function getBySurveyId($id)
    {
        $select = $this->where("{$this->table}.survey_id", $id)->orderBy("survey_question_position")->get();
        return $select;
    }
    /**
     * hàm lấy danh sách câu hỏi sorby theo vị trí câu hỏi và vị trí block
     *
     * @param [type] $id
     * @return void
     */
    public function getBySurveyIdAndSortby($id)
    {
        $select = $this->select(
            "{$this->table}.survey_question_id",
            "{$this->table}.survey_question_position",
            "{$this->table}.survey_block_id",
            "survey_block.survey_block_id",
            "survey_block.survey_block_position",
        );
        $select->where("{$this->table}.survey_id", $id)
            ->join("survey_block", "survey_block.survey_block_id", "{$this->table}.survey_block_id")
            ->orderBy("survey_block.survey_block_position")
            ->orderBy("{$this->table}.survey_question_position");
        return $select->get();
    }
    /**
     * Remove by survey_id
     * @param $id
     */
    public function removeBySurveyId($id)
    {
        $this->where("{$this->table}.survey_id", $id)->delete();
    }

    /**
     * Get by survey_block_id
     * @param $id
     * @return mixed
     */
    public function getBySurveyBlockId($id)
    {
        return $this->where("{$this->table}.survey_block_id", $id)->get();
    }

    // -------------------------------------------------------------------- RelationShip ----------------- ----- //
    public function answerQuestion()
    {
        return $this->hasMany(SurveyAnswerQuestionTable::class, 'survey_question_id');
    }

    public function singleChoice()
    {
        return $this->hasMany(SurveyQuestionChoiceTable::class, 'survey_question_id');
    }
}
