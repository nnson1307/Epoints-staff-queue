<?php

namespace Modules\Survey\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Survey\Repositories\JobNotify\JobNotifyRepoInterface;

class SaveNotificationSurvey implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $listUser;
    protected $survey;
    protected $typeUser;
    protected $typeNotifi;
    protected $tenantId;

    public function __construct($listUser, $survey, $typeUser, $typeNotifi, $tenantId)
    {
        $this->listUser = $listUser;
        $this->survey = $survey;
        $this->typeUser = $typeUser;
        $this->typeNotifi = $typeNotifi;
        $this->tenantId = $tenantId;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(JobNotifyRepoInterface $noti)
    {
        $noti->sendNotifi(
            $this->listUser,
            $this->survey,
            $this->typeUser,
            $this->typeNotifi,
            $this->tenantId
        );
    }
}
