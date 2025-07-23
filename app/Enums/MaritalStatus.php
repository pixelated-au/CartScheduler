<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum MaritalStatus: string
{
    case Single = 'single';
    case Married = 'married';
    case Separated = 'separated';
    case Divorced = 'divorced';
    case Widowed = 'widowed';
}
