<?php
namespace Modules\Notification\Entities;

use MyCore\Entities\JobMessageEntity;

/**
 * Class UnicastMessage
 * @package Modules\Notification\Entities
 * @author DaiDP
 * @since Aug, 2020
 */
class UnicastMessage extends JobMessageEntity
{
    public $tenant_id;

    public $staff_id;

    public $detail_id;

    public $title;

    public $message;

    public $avatar;

    public $schedule;

    public $notification_type = 'default';

    public $background = null;

    public $data = null;

    /**
     * UnicastMessage constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
