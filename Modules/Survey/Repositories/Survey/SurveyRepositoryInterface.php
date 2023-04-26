<?php


namespace Modules\Survey\Repositories\Survey;


interface SurveyRepositoryInterface
{
    /**
     * xử lý gửi thông báo notifi khảo sát
     * @param string $tenantId 
     */

    public function handleNotifiApply($tenantId);

    /**
     * Xử lý gửi thông báo khảo sát tính điểm
     * @param $tenantId 
     */

    public function handleNotifiAfterSurveyCountPoint($tenantId);
}
