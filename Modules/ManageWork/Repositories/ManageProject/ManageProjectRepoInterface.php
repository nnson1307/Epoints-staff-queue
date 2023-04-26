<?php


namespace Modules\ManageWork\Repositories\ManageProject;


interface ManageProjectRepoInterface
{
    /**
     * Cập nhật lại thông tin các dự án cũ
     * @return mixed
     */
    public function updateProject();
}