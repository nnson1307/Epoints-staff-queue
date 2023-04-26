<?php
namespace MyCore\SMS;

use MyCore\SMS\Viettel\ViettelConfig;
use MyCore\SMS\Viettel\ViettelSender;

/**
 * Class SMSSender
 * @package MyCore\SMS
 * @author DaiDP
 * @since Aug, 2019
 */
class SMSBuilder
{
    /**
     * Build SMS sender. Nếu muốn gửi SMS dùng hàm dưới
     *
     * $oSender = app('sms');
     * $oRs = $oSender->send('84987960024', 'Test noi dung SMS');
     *
     * @return ViettelSender
     * @throws SMSException
     */
    public function build(array $myConfig)
    {
        // Tạo đối tượng send sms
        $oSender = new ViettelSender(new ViettelConfig($myConfig));

        // Trả về
        return $oSender;
    }
}