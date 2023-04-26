<?php
namespace MyCore\Helper;

/**
 * Class OpensslCrypt
 * @package DaiDP\StsSDK\Support
 * @author DaiDP
 * @since Sep, 2019
 */
class OpensslCrypt
{
    protected $key;
    protected $iv;


    /**
     * OpensslCrypt constructor.
     * @param $key
     * @param $iv
     */
    public function __construct($key, $iv)
    {
        $this->key = base64_decode($key);
        $this->iv  = base64_decode($iv);
    }

    /**
     * Encode data
     *
     * @param $value
     * @return string
     */
    public function encode($value)
    {
        $strEnc = openssl_encrypt($value, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA , $this->iv);

        return base64_encode($strEnc);
    }

    /**
     * Decode data
     *
     * @param $value
     * @return string
     */
    public function decode($value)
    {
        $strDec = base64_decode($value);

        return openssl_decrypt($strDec, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA , $this->iv);
    }
}
