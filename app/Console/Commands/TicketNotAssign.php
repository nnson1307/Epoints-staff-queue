<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\ManageWork\Repositories\ManageWork\ManageWorkRepositoryInterface;
use Modules\Ticket\Models\BrandTable;
use Modules\Ticket\Repositories\Ticket\TicketRepositoryInterface;

/**
 * Class SendScheduleNotification
 * @package App\Console\Commands
 * @author DaiDP
 * @since Sep, 2019
 */
class TicketNotAssign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manageWork:notiTicketNotAssign';

    /**
     * The console command description
     *
     * @var string
     */
    protected $description = 'Ticket chưa phân công';



    public function handle(TicketRepositoryInterface $ticketRepo)
    {
        $mBrand = new BrandTable();
        $arrBrand = $mBrand->getAllBrand(env('IS_SAMPLE'));
        if (count($arrBrand) > 0) {
            foreach ($arrBrand as $v) {

                try {
                    $switchDb = switch_brand_db($v['tenant_id']);
                    if ($switchDb == true) {

                        //Xử lý đồng bộ dữ liệu cuộc gọi
                        $ticketRepo->ticketNotAssign();
                    }
                } catch (\Exception $e) {
                    Log::info($e->getMessage());
                    continue;
                }
            }

        }
    }
}