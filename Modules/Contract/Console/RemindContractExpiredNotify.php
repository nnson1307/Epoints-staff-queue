<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 26/11/2021
 * Time: 15:50
 */

namespace Modules\Contract\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Models\BrandTable;
use Modules\Contract\Models\ConfigTable;
use Modules\Contract\Repositories\ContractExpired\ContractExpiredRepoInterface;

class RemindContractExpiredNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epoint:contract-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Job quét nhắc nhở đến hạn HĐ';

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
     * @param ContractExpiredRepoInterface $contractExpired
     */
    public function handle(ContractExpiredRepoInterface $contractExpired)
    {
        Log::info('Start - Chạy job insert log đến hạn HĐ');

        $mBrand = new BrandTable();
        //Lấy thông tin brand
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));

        if (count($arrBrand) > 0) {
            foreach ($arrBrand as $v) {
                try {
                    $switchDb = switch_brand_db($v['tenant_id']);
                    if ($switchDb == true) {
                        $mConfig = new ConfigTable();
                        //Lấy cấu hình module hợp đồng
                        $config = $mConfig->getConfig('contract');
                        //Brand có sử dụng module HĐ
                        if ($config != null && $config['value'] == 1) {
                            //Chạy job lưu log đến hạn HĐ
                            $contractExpired->jobSaveLogContractExpired();
                        }
                    }
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                    continue;
                }
            }

            echo 'Chạy thành công';
            Log::info('End - Chạy job gửi thông báo hợp đồng');
        }
    }
}