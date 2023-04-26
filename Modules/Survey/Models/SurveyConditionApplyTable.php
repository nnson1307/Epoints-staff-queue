<?php

namespace Modules\Survey\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use MyCore\Models\Traits\ListTableTrait;

class SurveyConditionApplyTable extends Model
{   
    protected $connection = BRAND_CONNECTION;
    protected $table = 'survey_condition_apply';
    protected $primaryKey = 'id_survey_condition';
    protected $fillable = [
        'survey_id',
        'group_id',
        'type_group',
        'created_at',
        'updated_at'
    ];


   
}
