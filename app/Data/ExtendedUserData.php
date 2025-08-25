<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ExtendedUserData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $gender = null,
        public ?string $mobile_phone = null,
        public ?string $email = null,
        public ?string $marital_status = null,
        public ?string $appointment = null,
        public ?string $serving_as = null,
        public ?string $responsible_brother = null,
        public ?int $birth_year = null,
        public ?int $shift_id = null,
        public ?string $shift_date = null,
        public ?string $last_shift_date = null,
        public ?string $last_shift_start_time = null,
        public ?int $num_sundays = null,
        public ?int $num_mondays = null,
        public ?int $num_tuesdays = null,
        public ?int $num_wednesdays = null,
        public ?int $num_thursdays = null,
        public ?int $num_fridays = null,
        public ?int $num_saturdays = null,
        public ?int $filled_sundays = null,
        public ?int $filled_mondays = null,
        public ?int $filled_tuesdays = null,
        public ?int $filled_wednesdays = null,
        public ?int $filled_thursdays = null,
        public ?int $filled_fridays = null,
        public ?int $filled_saturdays = null,
        public ?string $availability_comments = null,
    ) {
    }
}
