<?php
namespace MyCore\Helper;

/**
 * Trait SwitchDatbase
 * @package MyCore\Helper
 * @author DaiDP
 * @since Oct, 2019
 */
trait SwitchDatbase
{
    /**
     * Chuyển database
     *
     * @param $idTenant
     * @return bool
     */
    protected function switchDatabase($idTenant)
    {
        $arrConnStr = config('sts-connstr', []);

        // Kiểm tra không tìm thấy cấu hình của tenant thì trả về lỗi
        if (empty($arrConnStr[$idTenant])) {
            return false;
        }

        // Kiểm tra connect string không đủ thông tin bắt buộc thì trả về lỗi 404
        $arrParams = $this->parseConnStr($arrConnStr[$idTenant]);
        if (empty($arrParams['server'])
            || empty($arrParams['database'])
            || empty($arrParams['user']))
        {
            return false;
        }

        // Thiết lập cấu hình database
        config([
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'host' => $arrParams['server'],
                'port' => $arrParams['port'] ?? 3306,
                'database' => $arrParams['database'],
                'username' => $arrParams['user'],
                'password' => $arrParams['password'] ?? '',
                'unix_socket' => env('DB_SOCKET', ''),
                'charset' => env('DB_CHARSET', 'utf8mb4'),
                'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => env('DB_PREFIX', ''),
                'strict' => env('DB_STRICT_MODE', true),
                'engine' => env('DB_ENGINE', null),
                'timezone' => env('DB_TIMEZONE', '+07:00'),
            ]
        ]);
        \DB::purge('mysql');

        return true;
    }


    /**
     * Parse connect string to array
     *
     * @param $str
     * @return array
     */
    protected function parseConnStr($str)
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