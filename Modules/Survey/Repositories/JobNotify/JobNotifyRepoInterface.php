<?php

/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 13/04/2021
 * Time: 14:29
 */

namespace Modules\Survey\Repositories\JobNotify;

use Modules\Survey\Models\SurveyAnswerTable;


interface JobNotifyRepoInterface
{
    /**
     * hàm gửi thông báo khảo sát
     * @param array $listUser 
     * @param object $survey
     * @param string $typeUser
     * @param string $typeNotifi
     * @param string $tenant_id
     * 
     */

    public function sendNotifi(
        $listUser,
        $survey,
        $typeUser,
        $typeNotifi,
        $tenant_id
    );

    /**
     * gửi thông báo kết quả khảo sát tính điểm
     * @param $sessionAnswer
     */

    public function sendNotifiResutlPoint(SurveyAnswerTable $sessionAnswer, $tenant_id);
}
