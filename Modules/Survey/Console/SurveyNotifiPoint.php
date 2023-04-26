<?php

namespace Modules\Survey\Console;

use Illuminate\Console\Command;
use Modules\Survey\Models\BrandTable;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Survey\Repositories\Survey\SurveyRepositoryInterface;

class SurveyNotifiPoint extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'epoint:survey_count_point';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo khảo sát đến nhân viên hoặc khách hàng sau khi khảo sát tính điểm';

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
    public function handle(SurveyRepositoryInterface $rSurvey)
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
                        $rSurvey->handleNotifiAfterSurveyCountPoint($v['tenant_id']);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
