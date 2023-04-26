<?php
namespace App\Http\Middleware;

use Closure;

/**
 * Class TrustIP
 * @package App\Http\Middleware
 * @author DaiDP
 * @since Sep, 2019
 */
class TrustIP
{
    /**
     * @var array
     */
    protected $ipWhiteList;

    public function __construct()
    {
        $ipConfigs = env('WHITE_LIST_IP');
        $ipConfigs = '0.0.0.0/0';
        $this->ipWhiteList = explode(',', $ipConfigs);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $clientIP = $request->getClientIp();

        // Check whilte list IP
        foreach ($this->ipWhiteList as $ipWL) {
            if (is_allow_ip($clientIP, $ipWL)) {
                return $next($request);
            }
        }

        abort(404);
    }
}
