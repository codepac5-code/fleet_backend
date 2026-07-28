<?php

namespace App\Http\Core\Classes\Event;

use Illuminate\Support\Str;

class DomainEvent
{
    public string $uuid;

    public function __construct(
        public string $type,
        public array $channels,
        public array $payload = [],
        ?string $uuid = null
    ) {
        $this->uuid = $uuid ?: (string) Str::uuid();
    }
}
