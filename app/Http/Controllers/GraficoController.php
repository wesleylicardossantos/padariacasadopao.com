<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\GraficoController as ApiGraficoController;
use App\Services\DashboardService;

class GraficoController extends ApiGraficoController
{
    public function __construct(DashboardService $dashboardService)
    {
        parent::__construct($dashboardService);
    }
}
