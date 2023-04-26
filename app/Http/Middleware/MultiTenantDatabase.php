<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\Log;

/**
 * Class MultiTenantDatabase
 * @package App\Http\Middleware
 * @author DaiDP
 * @since Sep, 2019
 */
class MultiTenantDatabase
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
        Log::info('[DMS call] ' . $request->url(), $request->all());

        // Lấy connect string và tenant id
        $idTenant   = $request->get('tenant_id');

        if (! switch_brand_db($idTenant)) {
            return $this->buildErrorResponse();
        }

        return $next($request);
    }

    /**
     * Build message error
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildErrorResponse()
    {
        return response()->json([
            'ErrorCode' => 2,
            'ErrorDescription' => 'Không tìm thấy tenant_id',
            'Data' => null
        ]);
    }
}
