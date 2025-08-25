<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ReportTagData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?int $sort = null,
    ) {
    }
}
