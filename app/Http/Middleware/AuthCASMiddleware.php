<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use phpCAS;

class AuthCASMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
//         $cas_config = array(
//             'host' => getenv('CAS_HOST'),
//             'context' => getenv('CAS_CONTEXT'),
//         );
//
//         phpCAS::client(CAS_VERSION_2_0, $cas_config['host'], 443, $cas_config['context']);
//         phpCAS::setNoCasServerValidation();
//
//         if ($request->has('logout')) {
//             phpCAS::logout();
//         } else if (phpCAS::isAuthenticated()) {
            return $next($request);
//         } else {
//             phpCAS::forceAuthentication();
//         }
    }
}
