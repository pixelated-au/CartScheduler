<?php

namespace App\Enums;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
enum ServingAs: string
{
    case FieldMissionary = 'field missionary';
    case SpecialPioneer = 'special pioneer';
    case BethelFamilyMember = 'bethel family member';
    case CircuitOverseer = 'circuit overseer';
    case RegularPioneer = 'regular pioneer';
    case Publisher = 'publisher';

}
