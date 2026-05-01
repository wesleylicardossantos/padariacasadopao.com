<?php

namespace Tests\Feature\Operations;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersMiddlewareTest extends TestCase
{
    public function test_middleware_applies_core_security_headers(): void
    {
        config()->set('hardening.security_headers.enable_hsts', false);

        $middleware = new SecurityHeaders();
        $request = Request::create('/healthz', 'GET');
        $response = $middleware->handle($request, fn () => new Response('ok', 200));

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy-Report-Only'));
    }
}
