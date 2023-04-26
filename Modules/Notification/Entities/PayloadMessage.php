<?php
namespace Modules\Notification\Entities;

use MyCore\Entities\JobMessageEntity;

/**
 * Class PayloadMessage
 * @package Modules\Notification\Entities
 * @author DaiDP
 * @since Aug, 2020
 */
class PayloadMessage extends JobMessageEntity
{
    public $tenant_id;

    public $title;

    public $message;

    public $badges;

    public $data = [];

    /**
     * PayloadMessage constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
