<?php
namespace MyCore\SMS\Viettel;

use MyCore\SMS\Configable;
use MyCore\SMS\SenderManager;
use MyCore\SMS\SMSException;

/**
 * Class ViettelSender
 * @package MyCore\SMS
 * @author DaiDP
 * @since Aug, 2019
 */
class ViettelSender implements SenderManager
{
    /**
     * @var ViettelConfig
     */
    protected $config;

    /**
     * Cấu hình thông tin gửi sms
     *
     * @param Configable $config
     * @return mixed
     */
    public function __construct(Configable $config)
    {
        if (! $config instanceof ViettelConfig) {
            throw new SMSException('The config must instance of MyCore\\SMS\\Viettel\\ViettelConfig');
        }

        $this->config = $config;
    }

    /**
     * Gửi SMS
     *
     * @param $phone
     * @param $message
     * @param null $idTracking
     * @return mixed
     */
    public function send($phone, $message, $idTracking = null)
    {
        return $this->sendMtBrandname($phone, $message, $idTracking);
    }


    /**
     * Gọi api viettel gui tin nhan
     *
     * @param $Phone
     * @param $Message
     * @param null $idTracking
     * @return array|bool
     */
    protected function sendMtBrandname($Phone, $Message, $idTracking = null)
    {
        $soap_request  = "<?xml version=\"1.0\"?>\n";
        $soap_request .= "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:impl=\"http://impl.bulkSms.ws/\">\n";
        $soap_request .= "  <soapenv:Header/>\n";
        $soap_request .= "  <soapenv:Body>\n";
        $soap_request .= "    <impl:wsCpMt>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <User>". $this->config->User ."</User>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <Password>". $this->config->Password ."</Password>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <CPCode>". $this->config->CPCode ."</CPCode>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <RequestID>". $idTracking ."</RequestID>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <UserID>". $Phone ."</UserID>\n"; // số điện thoại gửi
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <ReceiverID>". $Phone ."</ReceiverID>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <ServiceID>". $this->config->Brandname ."</ServiceID>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <CommandCode>". $this->config->CommandCode ."</CommandCode>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <Content>". $Message ."</Content>\n";
        $soap_request .= "      <!--Optional:-->\n";
        $soap_request .= "      <ContentType>". $this->config->ContentType ."</ContentType>\n";
        $soap_request .= "    </impl:wsCpMt>\n";
        $soap_request .= "  </soapenv:Body>\n";
        $soap_request .= "</soapenv:Envelope>";

        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($soap_request),
        );

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $this->config->Endpoint);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        10);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST,           true );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $soap_request);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);

        $output = curl_exec($soap_do);
        curl_close($soap_do);

        if($output === false)
        {
            return false;
        }
        else
        {
            return $this->parseSoapResponse($output);
        }
    }


    /**
     * Parse soap result
     *
     * @param string $xmlString
     * @return array
     */
    protected function parseSoapResponse($xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        $xml->registerXPathNamespace('S', 'http://schemas.xmlsoap.org/soap/envelope/');

        $arrData = array();
        foreach ($xml->xpath('//return') as $items)
        {
            foreach ($items as $item)
            {
                $arrData[$item->getName()] = $item->__toString();
            }
        }

        return $arrData;
    }
}