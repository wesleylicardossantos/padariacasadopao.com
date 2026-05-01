<?php

namespace App\Modules\PDV\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Tenancy\InteractsWithTenantContext;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    use InteractsWithTenantContext;

    public function __construct()
    {
        $this->middleware('tenant.context');
    }

    public function index(Request $request)
    {
        return view('enterprise.mobile_pdv.index', [
            'tenant' => $this->tenantSnapshot($request),
        ]);
    }
}
