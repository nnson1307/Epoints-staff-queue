<?php

namespace Modules\Survey\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyUserNotificationTable extends Model
{
    protected $table = "survey_user_notification";
    protected $connection = BRAND_CONNECTION;
    protected $fillable = [
        'survey_id',
        'user_id'
    ];
}
