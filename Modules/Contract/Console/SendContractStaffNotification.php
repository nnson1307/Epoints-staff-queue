<?php
/**
 * Created by PhpStorm.
 * User: nguyenngocson
 * Date: 16/07/2021
 * Time: 14:22
 */

namespace Modules\Contract\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Contract\Models\BrandTable;
use Modules\Contract\Models\ContractCareTable;
use Modules\Contract\Models\ContractStaffQueueTable;
use Modules\Contract\Models\ContractTable;
use Modules\Contract\Models\ConfigTable;
use Modules\ManageWork\Models\StaffNotificationDetailTable;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\PushNotification\PushNotificationInterface;
use Modules\Notification\Repositories\PushNotification\PushNotificationRepo;

class SendContractStaffNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epoint:send_contract_staff_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo nhân viên hợp đồng';

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
     */
    public function handle()
    {
        Log::info('Start - Chạy job gửi thông báo hợp đồng');

        $mBrand = new BrandTable();
        //Lấy thông tin brand
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));

        if (count($arrBrand) > 0) {
            foreach ($arrBrand as $v) {
                try {
                    $switchDb = switch_brand_db($v['tenant_id']);
                    if ($switchDb == true) {
                        $mConfig = new ConfigTable();
                        $config = $mConfig->getConfig('contract');
                        if($config != null && $config['value'] == 1){
                            $mContract = new ContractTable();
                            $mContractStaffQueue = new ContractStaffQueueTable();
                            $lstQueue = $mContractStaffQueue->getListStaffQueue();
                            foreach ($lstQueue as $queue) {
                                $dataMain = new \stdClass();
                                $dataMain->tenant_id = session()->get('idTenant');
                                $dataMain->staff_id = $queue['staff_id'];
                                $dataMain->detail_id = $queue['staff_notification_detail_id'];
                                $dataMain->title = $queue['staff_notification_title'];
                                $dataMain->message = $queue['staff_notification_message'];
                                $dataMain->avatar = '';
                                $dataMain->schedule = '';
                                $dataMain->notification_type = 'default';
                                $dataMain->background = null;

                                $mContractStaffQueue->updateSent(['is_send' => 1], $queue['contract_staff_queue_id']);
                                $message = new UnicastMessage((array)$dataMain);
                                $notiDetailRepo = app()->get(PushNotificationInterface::class);
                                $notiDetailRepo->unicast($message);
                            }

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