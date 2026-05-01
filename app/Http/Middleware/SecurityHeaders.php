<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => (string) config('hardening.security_headers.x_frame_options', 'SAMEORIGIN'),
            'Referrer-Policy' => (string) config('hardening.security_headers.referrer_policy', 'strict-origin-when-cross-origin'),
            'Permissions-Policy' => (string) config('hardening.security_headers.permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()'),
            'X-XSS-Protection' => '0',
        ];

        foreach ($headers as $name => $value) {
            if ($value !== '') {
                $response->headers->set($name, $value);
            }
        }

        $csp = trim((string) config('hardening.security_headers.content_security_policy_report_only', "default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: https: http:; img-src 'self' data: blob: https: http:; frame-ancestors 'self'; base-uri 'self'; form-action 'self' https: http:"));
        if ($csp !== '') {
            $response->headers->set('Content-Security-Policy-Report-Only', preg_replace('/\s+/', ' ', $csp));
        }

        $enableHsts = (bool) config('hardening.security_headers.enable_hsts', false);
        if ($enableHsts && ($request->isSecure() || strtolower((string) $request->headers->get('x-forwarded-proto')) === 'https')) {
            $maxAge = (int) config('hardening.security_headers.hsts_max_age', 31536000);
            $includeSubdomains = (bool) config('hardening.security_headers.hsts_include_subdomains', true);
            $preload = (bool) config('hardening.security_headers.hsts_preload', false);

            $value = 'max-age=' . $maxAge;
            if ($includeSubdomains) {
                $value .= '; includeSubDomains';
            }
            if ($preload) {
                $value .= '; preload';
            }

            $response->headers->set('Strict-Transport-Security', $value);
        }

        return $response;
    }
}
