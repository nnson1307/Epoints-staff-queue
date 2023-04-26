<?php
namespace MyCore\SMS\Viettel;

use MyCore\SMS\Configable;

/**
 * Class ViettelConfig
 * @package MyCore\SMS\Viettel
 * @author DaiDP
 * @since Aug, 2019
 */
class ViettelConfig extends Configable
{
    public $User;

    public $Password;

    public $CPCode;

    public $CommandCode;

    public $ContentType;

    public $Brandname;

    public $Endpoint = 'http://ams.tinnhanthuonghieu.vn:8009/bulkapi';


    /**
     * ViettelConfig constructor.
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        parent::__construct($configs);
    }
}