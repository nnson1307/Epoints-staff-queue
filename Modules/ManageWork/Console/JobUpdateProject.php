<?php

namespace Modules\ManageWork\Console;

use Illuminate\Console\Command;
use Modules\ManageWork\Repositories\ManageProject\ManageProjectRepoInterface;
use Modules\Survey\Models\BrandTable;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class JobUpdateProject extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'project:job_update_project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Job update project chỉ chạy 1 lần cập nhật thông tin dự án cũ cho các giá trị mặc dịnh';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(ManageProjectRepoInterface $rManageProject)
    {
        $mBrand = new BrandTable();
        //Lấy thông tin brand
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));

        if (count($arrBrand) > 0) {
            foreach ($arrBrand as $v) {

                try {
                    $switchDb = switch_brand_db($v['tenant_id']);
                    if ($switchDb == true) {
                        // xử lý gửi thông báo
                        $rManageProject->updateProject();
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
