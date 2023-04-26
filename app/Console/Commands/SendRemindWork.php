<?php
namespace App\Console\Commands;

use App\Entities\BroadcastBrandMessage;
use App\Entities\BroadcastMessage;
use App\Entities\SendGroupMessage;
use App\Entities\UnicastUserMessage;
use App\Jobs\BroadcastBrandJob;
use App\Jobs\BroadcastJob;
use App\Jobs\SendGroupJob;
use App\Jobs\UnicastUserJob;
use App\Models\NotificationQueueTable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\ManageWork\Models\BrandTable;
use Modules\ManageWork\Repositories\ManageWork\ManageWorkRepositoryInterface;

/**
 * Class SendScheduleNotification
 * @package App\Console\Commands
 * @author DaiDP
 * @since Sep, 2019
 */
class SendRemindWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manageWork:sendRemindWork';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Kiểm tra và gửi noti nhắc nhở';



    public function handle(ManageWorkRepositoryInterface $manageWorkRepo)
    {
        $mBrand = new BrandTable();
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));
        if (count($arrBrand) > 0) {
            foreach ($arrBrand as $v) {

                try {
                    $switchDb = switch_brand_db($v['tenant_id']);
                    if ($switchDb == true) {

                        //Xử lý đồng bộ dữ liệu cuộc gọi
                        $manageWorkRepo->sendRemindWork();
                    }
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                    continue;
                }
            }

        }
    }
}