<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\JobNotify\Repositories\NotifyStaff\NotifyStaffRepoInterface;

class FunctionSendNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * FunctionSendNotify constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function handle()
    {
        $input = $this->data;

        $switchDb = switch_brand_db($input['tenant_id']);

        if ($switchDb == true) {
            switch ($input['type']) {
                case 'notify_staff';
                    //Gửi thông báo nhân viên
                    $mStaffRepo = app()->get(NotifyStaffRepoInterface::class);

                    $mStaffRepo->saveLogNotifyStaff($input);
                    break;
            }
        }
    }
}
