<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class StubController extends Controller
{
    public function __call($name, $arguments): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint placeholder during project migration.',
            'controller' => static::class,
            'method' => $name,
        ], 501);
    }

    public function __invoke(...$arguments): JsonResponse
    {
        return $this->__call('__invoke', $arguments);
    }
}
