<?php

namespace App\Imports;

use App\Actions\GetUserValidationUtils;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/*******************************************************************************************************************
 * @see \App\Exports\UsersExport CHANGES IN THIS FILE NEED HERE MAY NEED TO BE REFLECTED HERE
 *******************************************************************************************************************/
class UsersImport implements WithHeadingRow, WithValidation, WithBatchInserts, OnEachRow
{
    private int $createCount = 0;
    private int $updateCount = 0;

    public function onRow(Row $row): void
    {
        $rowData = $row->toArray();

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
            'is_unrestricted'     => $rowData['is_unrestricted'] ?? true,
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

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @noinspection PhpUnusedParameterInspection
     */
    public function prepareForValidation(array $data, int $index): array
    {
        $data['role'] = Role::User->value;

        return app()->make(GetUserValidationUtils::class)->prepare($data);
    }

    public function rules(): array
    {
        $validationUtils = app()->make(GetUserValidationUtils::class);
        $rules           = $validationUtils->rules();

        $rules['spouse_email'] = ['nullable', 'email'];
        $rules['spouse_id']    = ['nullable', 'exists:users,id'];

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $data   = $validator->getData();
        $key    = key($data);
        $data   = current($data);

        $validator->after(
            (app()->make(GetUserValidationUtils::class))->extraValidation($key, $data)
        );
    }

    private function tidyNullableData(?string $datum): ?string
    {
        return (!$datum || !trim($datum)) ? null : $datum;
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
