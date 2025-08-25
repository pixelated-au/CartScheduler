<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UserAdminData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
        public ?string $gender = null,
        public ?string $mobile_phone = null,
        public ?int $year_of_birth = null,
        public ?string $appointment = null,
        public ?string $serving_as = null,
        public ?string $marital_status = null,
        public ?string $spouse_name = null,
        public ?int $spouse_id = null,
        public bool $is_enabled = true,
        public bool $is_unrestricted = false,
        public ?string $responsible_brother = null,
        public bool $has_logged_in = false,
        /** @var Collection<int, \App\Data\UserVacationData> */
        public ?Collection $vacations = null,
        public AvailabilityData|Optional $availability,
        /** @var Collection<int, int> */
        public ?Collection $selectedLocations = null,
    ) {
    }
}
