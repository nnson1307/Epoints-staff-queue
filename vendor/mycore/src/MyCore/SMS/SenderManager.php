<?php
namespace MyCore\SMS;

/**
 * Interface SenderManager
 * @package MyCore\SMS
 * @inheritdoc https://designpatternsphp.readthedocs.io/en/latest/Creational/Builder/README.html
 * @author DaiDP
 * @since Aug, 2019
 */
interface SenderManager
{
    /**
     * Cấu hình thông tin gửi sms
     *
     * @param Configable $config
     * @return mixed
     */
    public function __construct(Configable $config);

    /**
     * Gửi SMS
     *
     * @param $phone
     * @param $message
     * @param null $idTracking
     * @return mixed
     */
    public function send($phone, $message, $idTracking = null);
}