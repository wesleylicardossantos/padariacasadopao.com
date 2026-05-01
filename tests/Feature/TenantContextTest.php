<?php

namespace Tests\Feature;

use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    public function test_empresa_id_is_resolved_from_request_first(): void
    {
        $request = Request::create('/fake', 'GET', ['empresa_id' => 99]);

        $this->assertSame(99, TenantContext::empresaId($request));
    }
}
