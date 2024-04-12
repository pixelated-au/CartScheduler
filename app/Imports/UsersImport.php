<?php

namespace App\Imports;

use App\Actions\GetUserValidationPreparations;
use App\Enums\Appontment;
use App\Enums\MaritalStatus;
use App\Enums\ServingAs;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class UsersImport implements WithHeadingRow, WithValidation, WithBatchInserts, OnEachRow
{
    private int $createCount = 0;
    private int $updateCount = 0;

    /*******************************************************************************************************************
     *******************************************************************************************************************
     * @param Row $row Each row in the spreadsheet
     *
     * @see \App\Exports\UsersExport CHANGES IN THIS FILE NEED HERE MAY NEED TO BE REFLECTED HERE
     */

    public function onRow(Row $row): void
    {
        $rowData = $row->toArray();

        /**
         * @noinspection PhpIssetCanBeReplacedWithCoalesceInspection
         * @noinspection NullCoalescingOperatorCanBeUsedInspection
         */
        $data = [
            'name'                => $rowData['name'],
            'email'               => $rowData['email'],
            'mobile_phone'        => $rowData['mobile_phone'],
            'gender'              => $rowData['gender'],
            'year_of_birth'       => $this->tidyNullableData($rowData['year_of_birth']),
            'appointment'         => $this->tidyNullableData($rowData['appointment']),
            'serving_as'          => $this->tidyNullableData($rowData['serving_as']),
            'marital_status'      => $this->tidyNullableData($rowData['marital_status']),
            'spouse_id'           => $this->tidyNullableData($rowData['spouse_id']),
            'responsible_brother' => $rowData['responsible_brother'] ?: false,
            'is_unrestricted'     => isset($rowData['is_unrestricted']) ? $rowData['is_unrestricted'] : true,
        ];

        $user = User::where('email', '=', $rowData['email'])->first();
        if ($user) {
            $user->update($data);
            ++$this->updateCount;

        } else {
            $data['uuid']     = Str::uuid();
            $data['password'] = null;
            $user             = User::create($data);
            $user->availability()->create();
            ++$this->createCount;
        }
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email'],
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
     * @noinspection PhpUnusedParameterInspection
     */
    public function prepareForValidation(array $data, int $index): array
    {
        $getUserValidationPreparations = app()->make(GetUserValidationPreparations::class);

        return $getUserValidationPreparations->execute($data);
    }

    public function headingRow(): int
    {
        return 2;
    }

    public function getCreateCount(): int
    {
        return $this->createCount;
    }

    public function getUpdateCount(): int
    {
        return $this->updateCount;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
