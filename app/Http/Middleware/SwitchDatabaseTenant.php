<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

/**
 * Class SwitchDatabaseTenant
 * @package App\Http\Middleware
 * @author DaiDP
 * @since Sep, 2019
 */
class SwitchDatabaseTenant
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Lấy connect string và tenant id
        $idTenant   = $request->route('tenantid');
        $arrConnStr = config('sts-connstr', []);

        // Kiểm tra không tìm thấy cấu hình của tenant thì trả về lỗi 404
        if (empty($arrConnStr[$idTenant])) {
            abort(404);
        }

        // Kiểm tra connect string không đủ thông tin bắt buộc thì trả về lỗi 404
        $arrParams = $this->parseConnStr($arrConnStr[$idTenant]);
        if (empty($arrParams['server'])
            || empty($arrParams['database'])
            || empty($arrParams['user']))
        {
            abort(404);
        }

        session(['idTenant' => $idTenant]);

        switch_config($arrParams);

        return $next($request);
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
