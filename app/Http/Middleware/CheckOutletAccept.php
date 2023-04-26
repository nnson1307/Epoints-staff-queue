<?php

namespace App\Http\Middleware;

use App\Exceptions\OutletNotAcceptException;
use App\Models\OutletMasterTable;
use Illuminate\Http\Request;
use Closure;

/**
 * Class CheckOutletAccept
 * @package App\Http\Middleware
 * @author DaiDP
 * @since Apr, 2020
 */
class CheckOutletAccept
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws OutletNotAcceptException
     */
    public function handle(Request $request, Closure $next)
    {
        $oUser    = auth()->user();
        $mOutlet  = app()->get(OutletMasterTable::class);
        $isActive = $mOutlet->checkActive($oUser->outlet_id);

        if ($isActive === 0) {
            throw new OutletNotAcceptException('Cửa hàng của bạn đã bị ngưng kết nối với thương hiệu. Vui lòng liên hệ với thương hiệu hoặc nhân viên kinh doanh để được hỗ trợ!');
        }

        return $next($request);
    }
}
