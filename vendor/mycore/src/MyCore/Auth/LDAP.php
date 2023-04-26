<?php
namespace MyCore\Auth;

use Illuminate\Support\Facades\Log;

/**
 * Class LDAP
 * @package MyCore\Auth
 * @author DaiDP
 * @since Jul, 2019
 * @see https://www.php.net/manual/en/ldap.examples-basic.php
 */
class LDAP
{
    /**
     * LDAP host
     * @var string
     */
    public $ldap_host = '172.20.23.53';

    /**
     * LDAP port. Default 389
     * @var int
     */
    public $ldap_port = 389;

    /**
     * Search query. Use with ldap_search to get user info. To get more code, visit at: https://www.php.net/manual/en/intro.ldap.php
     * OU=Departments,DC=college,DC=fpt,DC=com.vn
     * @var string
     */
    public $ldap_dn = '';

    /**
     * active directory user group name
     * @var string
     */
    public $ldap_user_group = 'WebUsers';

    /**
     * active directory manager group name
     * @var string
     */
    public $ldap_manager_group = 'WebManagers';

    /**
     * User Email domain. Fill it to login with email. Leave empty if login with full email address
     * @var string
     */
    public $ldap_usr_dom = '';


    /**
     * Authen with email
     *
     * @param $user
     * @param $password
     * @return bool
     */
    function authenticate($user, $password)
    {
        // Check required info
        $user = trim($user);
        if (empty($user) || empty($password)) {
            return false;
        }

        $arDM = explode('@', $user);
        $domain = end($arDM);
        $domain = strtolower($domain);

        if ($domain == 'fpt.net' || $domain == 'vienthongtin.com') {
            $this->ldap_host = '172.20.23.53';
            $this->ldap_port = 390;
        }
        elseif ($domain == 'opennet.com.kh') {
            $this->ldap_host = '172.20.23.53';
            $this->ldap_port = 391;
        }

        // connect to active directory
        try {
            $ldap = ldap_connect(sprintf('ldap://%s:%s', $this->ldap_host, $this->ldap_port));
        } catch (\Exception $ex) {
            Log::error('[LDAP Connect] ' . $ex->getMessage());
            return false;
        }

        // Check connect result
        if (! $ldap) {
            Log::error('[LDAP Connect] ');
            return false;
        }

        // configure ldap params
        ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
        ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);

        // verify user and password
        return @ldap_bind($ldap, $user.$this->ldap_usr_dom, $password);
    }
}