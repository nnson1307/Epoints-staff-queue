<?php
namespace MyCore\Http\Middleware;

use Closure;

/**
 * Class DetectLanguage
 * @package MyCore\Http\Middleware
 * @author DaiDP
 * @since May, 2020
 */
class DetectLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $lang = $request->header('lang', \App::getLocale());
        \App::setLocale($lang);

        return $next($request);
    }
}