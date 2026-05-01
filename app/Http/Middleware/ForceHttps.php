<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.force_https') || app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        $forwardedProto = strtolower((string) $request->headers->get('x-forwarded-proto', ''));
        $cfVisitor = strtolower((string) $request->headers->get('cf-visitor', ''));
        $alreadySecure = $request->isSecure()
            || $forwardedProto === 'https'
            || str_contains($cfVisitor, '"scheme":"https"');

        if (!$alreadySecure) {
            $secureUrl = preg_replace('/^http:\/\//i', 'https://', $request->fullUrl());
            if ($secureUrl && $secureUrl !== $request->fullUrl()) {
                return redirect()->to($secureUrl, 301);
            }
        }

        return $next($request);
    }
}
