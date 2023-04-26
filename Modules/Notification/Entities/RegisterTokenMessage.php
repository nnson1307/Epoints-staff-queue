<?php
namespace Modules\Notification\Entities;

use MyCore\Entities\JobMessageEntity;

/**
 * Class RegisterTokenMessage
 * @package Modules\Notification\Entities
 * @author DaiDP
 * @since Aug, 2020
 */
class RegisterTokenMessage extends JobMessageEntity
{
    /**
     * @var string
     */
    public $tenant_id;
    /**
     * @var int
     */
    public $staff_id;

    /**
     * @var string
     */
    public $platform;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $imei;

    /**
     * UnicastMessage constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }
}
