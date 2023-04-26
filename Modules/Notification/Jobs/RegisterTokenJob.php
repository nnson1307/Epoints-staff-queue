<?php
namespace Modules\Notification\Jobs;

use Modules\Notification\Entities\RegisterTokenMessage;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Repositories\Register\RegisterInterface;
use MyCore\Jobs\BaseJob;

/**
 * Class RegisterTokenJob
 * @package Modules\Notification\Jobs
 * @author DaiDP
 * @since Aug, 2020
 */
class RegisterTokenJob extends BaseJob
{
    public $queue = 'noti';
    public $tries = 1;

    /**
     * @var UnicastMessage
     */
    protected $message;


    /**
     * UnicastUserJob constructor.
     * @param RegisterTokenMessage $message
     */
    public function __construct(RegisterTokenMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     * @param RegisterInterface $register
     */
    public function handle(RegisterInterface $register)
    {
        $register->register($this->message);
    }
}
