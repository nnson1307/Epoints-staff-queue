<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Commission\Models\ServiceBrandFeatureChildTable;
use Modules\Commission\Repositories\StaffCommission\StaffCommissionRepoInterface;
use Modules\Commission\Models\BrandTable;

class CalculateStaffCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commission:calculate-staff';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Tính hoa hồng cho nhân viên';



    public function handle(StaffCommissionRepoInterface $staffCommission)
    {
        $mBrand = new BrandTable();

        //Lấy ds brand
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));

        if (count($arrBrand) > 0) {
            $mBrandFeatureChild = app()->get(ServiceBrandFeatureChildTable::class);

            foreach ($arrBrand as $v) {

                try {
                    //Check quyền site
//                    $getService = $mBrandFeatureChild->getServiceBrand($v['brand_id'], 'admin.commission');
//
//                    if ($getService == null) {
//                        echo $v['brand_code'] . ' không có sử dụng dịch vụ';
//
//                        continue;
//                    }

                    $switchDb = switch_brand_db($v['tenant_id']);

                    if ($switchDb == true) {

                        //Xử lý tính hoa hồng cho nhân viên
                        $staffCommission->calculateStaffCommission();

                        echo 'Chạy thành công' . $v['brand_code'];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

        }
    }
}