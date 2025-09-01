<?php

namespace App\Data;

use App\Enums\Appointment;
use App\Enums\MaritalStatus;
use App\Enums\ServingAs;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserAdminData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        #[LiteralTypeScriptType('male | female | undefined')]
        public string|Optional $gender,
        public string|Optional $mobile_phone,
        public int|Optional $year_of_birth,
        public Appointment|Optional $appointment,
        public ServingAs|Optional $serving_as,
        public MaritalStatus|Optional $marital_status,
        public string|Optional $responsible_brother,
        /** @var Collection<int, \App\Data\UserVacationData> */
        public Collection|Optional $vacations,
        public AvailabilityData|Optional $availability,
        /** @var Collection<int, int> */
        public Collection|Optional $selectedLocations,
        public SpouseAdminData|Optional $spouse,
        public bool $is_enabled = true,
        public bool $is_unrestricted = false,
        public bool $has_logged_in = false,
    ) {
    }
}
