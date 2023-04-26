<?php
use \Illuminate\Support\Facades\DB;
use App\Models\Brand\ConfigTable;
use App\Models\BrandTable;

if ( ! function_exists('config_path'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if ( ! function_exists('match_cidr'))
{
    function match_cidr($ipAddr, $cidr)
    {
        list($ip, $mask) = explode('/', $cidr);
        $mask = 0xffffffff << (32 - $mask);
        return ((ip2long($ipAddr) & $mask) == (ip2long($ip) & $mask));
    }
}

if ( ! function_exists('is_allow_ip'))
{
    function is_allow_ip($ipClient, $ipWhite)
    {
        if (empty($ipWhite) || empty($ipClient)) {
            return false;
        }

        if (strpos('/', $ipWhite) === false) {
            $ipWhite .= '/32';
        }

        return match_cidr($ipClient, $ipWhite);
    }
}

if ( ! function_exists('switch_brand_db'))
{
    function switch_brand_db($idTenant)
    {
        //$arrConnStr = config('sts-connstr', []);
        $arrConnStr = include config_path('epoint-connstr.php');

        // Kiểm tra không tìm thấy cấu hình của tenant thì trả về lỗi
        if (empty($arrConnStr[$idTenant])) {
            return false;
        }

        // Kiểm tra connect string không đủ thông tin bắt buộc thì trả về lỗi 404
        $arrParams = parse_conn_str($arrConnStr[$idTenant]);

        if (empty($arrParams['server'])
            || empty($arrParams['database'])
            || empty($arrParams['user']))
        {
            return false;
        }

        session(['idTenant' => $idTenant]);

        switch_config($arrParams);

        return true;
    }
}

if ( ! function_exists('switch_config'))
{
    function switch_config($arrParams)
    {
        // Thiết lập cấu hình database
        config([
            'database.connections.brand' => [
                'driver' => 'mysql',
                'host' => $arrParams['server'],
                'port' => $arrParams['port'] ?? 3306,
                'database' => $arrParams['database'],
                'username' => $arrParams['user'],
                'password' => $arrParams['password'] ?? '',
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'strict' => false,
                'engine' => env('DB_ENGINE', null),
                'timezone' => env('DB_TIMEZONE', '+07:00'),
            ]
        ]);

        //clear cache
        DB::purge('brand');
        // thiết lập config cho AWS noti
        $mConfig = new ConfigTable();
        $arrConfig = $mConfig->getAll();

        if (isset($arrConfig['noti_staff_android_arn']) && $arrConfig['noti_staff_android_arn'] != ''
            && isset($arrConfig['noti_staff_ios_arn']) && $arrConfig['noti_staff_ios_arn'] != ''
            && isset($arrConfig['noti_staff_topic_arn']) && $arrConfig['noti_staff_topic_arn'] != '') {
            config([
                'services.aws_sns' => [
                    'android_arn' => $arrConfig['noti_staff_android_arn'],
                    'ios_arn' => $arrConfig['noti_staff_ios_arn'],
                    'topic_arn' => $arrConfig['noti_staff_topic_arn'],
                ]
            ]);

            \DB::purge('services');
        }
    }
}

if ( ! function_exists('parse_conn_str'))
{
    function parse_conn_str($str)
    {
        $arrPart   = explode(';', $str);
        $arrParams = [];

        foreach ($arrPart as $item) {
            list($key, $val) = explode('=', $item, 2);
            $key = strtolower($key);

            $arrParams[$key] = $val;
        }

        return $arrParams;
    }
}

if ( ! function_exists('bind_params_template'))
{
    function bind_params_template($content, array $params)
    {
        $findVal    = array_keys($params);
        $replaceVal = array_values($params);

        foreach ($findVal as &$item) {
            $item = sprintf('[:%s]', $item);
        }

        return str_replace($findVal, $replaceVal, $content);
    }
}

if (! function_exists('mysqldb_options')) {
    /**
     * Lay option mysql db
     *
     * @return array
     */
    function mysqldb_options()
    {
        $enableSSL = env('DB_SSL', true);
        if ($enableSSL !== false) {

            return [
                PDO::MYSQL_ATTR_SSL_KEY => database_path('BaltimoreCyberTrustRoot.crt.pem')
            ];
        }

        return [];
    }
}
