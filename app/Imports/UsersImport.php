<?php

namespace App\Imports;

use App\Actions\GetUserValidationPreparations;
use App\Enums\Appontment;
use App\Enums\MaritalStatus;
use App\Enums\ServingAs;
use App\Models\User;
use App\Models\UserAvailability;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts
{
    private int $rowCount = 0;

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $user = User::create([
                'uuid'                => Str::uuid(),
                'name'                => $row['name'],
                'email'               => $row['email'],
                'mobile_phone'        => $row['mobile_phone'],
                'gender'              => $row['gender'],
                'year_of_birth'       => $this->tidyNullableData($row['year_of_birth']),
                'appointment'         => $this->tidyNullableData($row['appointment']),
                'serving_as'          => $this->tidyNullableData($row['serving_as']),
                'marital_status'      => $this->tidyNullableData($row['marital_status']),
                'spouse_id'           => $this->tidyNullableData($row['spouse_id']),
                'responsible_brother' => $row['responsible_brother'] ?: false,
                'password'            => null,
                'is_unrestricted'     => $row['is_unrestricted'] ?: false,
            ]);

            UserAvailability::create(['user_id' => $user->id]);
            ++$this->rowCount;
        }
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'mobile_phone'        => ['required', 'string', 'regex:/^([0-9\+\-\s]+)$/', 'min:8', 'max:15'],
            'gender'              => ['required', 'in:male,female,m,f'],
            'year_of_birth'       => ['nullable', 'integer', 'min:' . date('Y') - 100, 'max:' . date('Y')],
            'appointment'         => ['nullable', 'string', new Enum(Appontment::class)],
            'serving_as'          => ['nullable', 'string', new Enum(ServingAs::class)],
            'marital_status'      => ['nullable', 'string', new Enum(MaritalStatus::class)],
            'spouse_email'        => ['nullable', 'email'],
            'spouse_id'           => ['nullable', 'exists:users,id'],
            'responsible_brother' => ['nullable', 'boolean'],
            'is_unrestricted'     => ['nullable', 'boolean'],
        ];
    }

    private function tidyNullableData(?string $datum): ?string
    {
        return (!$datum || !trim($datum)) ? null : $datum;
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function prepareForValidation(array $data, int $index)
    {
        $getUserValidationPreparations = app()->make(GetUserValidationPreparations::class);

        return $getUserValidationPreparations->execute($data);
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
