<?php
namespace Modules\Notification\Entities;

use MyCore\Entities\JobMessageEntity;

/**
 * Class SendTopicMessage
 * @package Modules\Notification\Entities
 * @author DaiDP
 * @since Aug, 2020
 */
class SendTopicMessage extends JobMessageEntity
{
    public $tenant_id;

    public $title;

    public $message;

    public $avatar;

    public $schedule;

    public $topic;

    /**
     * SendTopicMessage constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
