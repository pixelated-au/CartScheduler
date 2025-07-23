<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum Appointment: string
{
    case Elder = 'elder';
    case MinisterialServant = 'ministerial servant';
}
