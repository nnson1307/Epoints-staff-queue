<?php

namespace Modules\Commission\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Commission\Models\BrandTable;
use Modules\Commission\Models\ServiceBrandFeatureChildTable;
use Modules\Commission\Repositories\StaffCommission\StaffCommissionRepoInterface;

class CommissionController extends Controller
{
    public function calculateCommission()
    {
        $mBrand = new BrandTable();
        $mStaffCommission = app()->get(StaffCommissionRepoInterface::class);

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
//                        continue;h
//                    }

                    $switchDb = switch_brand_db($v['tenant_id']);

                    if ($switchDb == true) {

                        //Xử lý tính hoa hồng cho nhân viên
                        $mStaffCommission->calculateStaffCommission();

                        echo 'Chạy thành công' . $v['brand_code'];
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

        }
    }
}
