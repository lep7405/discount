<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpChecker
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        info('ip: ' . $ip);
        $ip_server_env = config('discount_manager.affiliate_partner_ips');
        $ip_servers = explode(',', $ip_server_env);

        if(in_array($ip, $ip_servers)) {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'Forbidden'
        ]);
    }
}
