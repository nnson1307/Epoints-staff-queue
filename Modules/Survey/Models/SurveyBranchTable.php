<?php


namespace Modules\Survey\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class SurveyBranchTable extends Model
{
    use ListTableTrait;
    protected $connection = BRAND_CONNECTION;
    public $timestamps = false;
    protected $table = "survey_branch";
    protected $primaryKey = "survey_branch_id";

    protected $fillable = [
        'survey_branch_id',
        'survey_id',
        'branch_id',
        'target_user',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    public function addInsert($data)
    {
        $this->insert($data);
    }

    public function getAll($id)
    {
        $select = $this->select("{$this->table}.*")
            ->where("{$this->table}.survey_id", $id)->get();
        return $select;
    }

    public function remove($id)
    {
        $this->where($this->table . '.survey_id', $id)->delete();
    }
}
