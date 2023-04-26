<?php


namespace Modules\ManageWork\Repositories\ManageProject;


use Carbon\Carbon;
use Modules\ManageWork\Models\ManageProjectStaffTable;
use Modules\ManageWork\Models\ManageProjectTable;
use Modules\ManageWork\Models\StaffTable;

class ManageProjectRepo implements ManageProjectRepoInterface
{
    /**
     * Cập nhật lại thông tin các dự án cũ
     * @return mixed|void
     */
    public function updateProject()
    {
//        Lấy danh sách dự án
        $mManageProject = app()->get(ManageProjectTable::class);
        $mManageProjectStaff = app()->get(ManageProjectStaffTable::class);
        $mStaff = app()->get(StaffTable::class);

        $staffAdmin = $mStaff->getStaffAdmin();
        $allStaff = $mStaff->getAllStaffNotAdmin($staffAdmin['staff_id']);

        $getAllProject = $mManageProject->getAll();
        foreach ($getAllProject as $item){
            $prefixCode = null;
            $projectName = explode(' ',$item['manage_project_name']);
            $number = 2;
            if (count($projectName) > 1){
                $prefixCode = substr($projectName[0],0,1).substr($projectName[1],0,1);
            } else {
                if (strlen($projectName[0]) >= 2){
                    $prefixCode = substr($projectName[0],0,2);
                } else {
                    $prefixCode = substr($projectName[0],0,1);
                    $number = 3;
                }
            }

            $prefixCode = $this->generate_string($prefixCode,$number);


            $data = [
                'manager_id' => $staffAdmin['staff_id'],
                'department_id' => $staffAdmin['department_id'],
                'date_start' => Carbon::now()->format('Y-m-d'),
                'date_end' => Carbon::now()->addMonths(5)->format('Y-m-d'),
                'color_code' => '#ff0000',
                'permission' => 'public',
                'prefix_code' => $prefixCode,
                'manage_project_status_id' => 1,
                'is_active' => 1,
                'is_deleted' => 0
            ];

//            Cập nhật dự án
            $mManageProject->updateProject($data,$item['manage_project_id']);
            $dataStaff = [];
            foreach ($allStaff as $itemStaff){
                $dataStaff[] = [
                    'manage_project_id' => $item['manage_project_id'],
                    'staff_id' => $itemStaff['staff_id'],
                    'manage_project_role_id' => 2,
                    'created_at' => Carbon::now(),
                    'created_by' => $staffAdmin['staff_id'],
                    'updated_at' => Carbon::now(),
                    'updated_by' => $staffAdmin['staff_id'],
                ];
            }

            $dataStaff[count($dataStaff)+1] = [
                'manage_project_id' => $item['manage_project_id'],
                'staff_id' => $staffAdmin['staff_id'],
                'manage_project_role_id' => 1,
                'created_at' => Carbon::now(),
                'created_by' => $staffAdmin['staff_id'],
                'updated_at' => Carbon::now(),
                'updated_by' => $staffAdmin['staff_id'],
            ];

            $mManageProjectStaff->insertStaff($dataStaff);
        }
    }


    function generate_string($input, $strength = 2) {
        $mManageProject = app()->get(ManageProjectTable::class);
        $permitted_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        $random_string = $input.$random_string;
//        Kiểm tra mã code có bị trùng
        $checkCode = $mManageProject->checkCode($random_string);

        if (count($checkCode) != 0){
            $this->generate_string($input, $strength);
        }

        return $random_string;
    }
}