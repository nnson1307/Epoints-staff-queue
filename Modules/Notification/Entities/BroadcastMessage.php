<?php
namespace Modules\Notification\Entities;

use MyCore\Entities\JobMessageEntity;

/**
 * Class BroadcastMessage
 * @package Modules\Notification\Entities
 * @author DaiDP
 * @since Aug, 2020
 */
class BroadcastMessage extends JobMessageEntity
{
    public $tenant_id;

    public $title;

    public $message;

    public $avatar;

    public $schedule;

    /**
     * BroadcastMessage constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
