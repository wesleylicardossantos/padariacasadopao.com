<?php

namespace App\Modules\PDV\Data;

use Illuminate\Http\Request;

class SyncMonitorFilterData
{
    public function __construct(
        public readonly string $status,
        public readonly string $uuidLocal,
        public readonly int $perPage,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $perPage = (int) $request->get('per_page', 20);
        $perPage = max(10, min($perPage, 100));

        return new self(
            status: trim((string) $request->get('status', '')),
            uuidLocal: trim((string) $request->get('uuid_local', '')),
            perPage: $perPage,
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'uuid_local' => $this->uuidLocal,
            'per_page' => $this->perPage,
        ];
    }
}
