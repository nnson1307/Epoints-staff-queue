<?php

namespace Modules\Survey\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Survey\Repositories\JobNotify\JobNotifyRepoInterface;

class SendNotificationPointSurvey implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $sessionAnswer;
    protected $tenantId;

    public function __construct($sessionAnswer, $tenantId)
    {
        $this->sessionAnswer = $sessionAnswer;
        $this->tenantId = $tenantId;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(JobNotifyRepoInterface $noti)
    {
        $noti->sendNotifiResutlPoint(
            $this->sessionAnswer,
            $this->tenantId
        );
    }
}
