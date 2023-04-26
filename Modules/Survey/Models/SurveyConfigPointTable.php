<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyConfigPointTable extends Model
{
    protected $table = "survey_config_point";
    protected $connection = BRAND_CONNECTION;

    const SHOW_ANSWER_END = 'E';
    const SHOW_ANSWER_BETWEEN = 'C';
}
