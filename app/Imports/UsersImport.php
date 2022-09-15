<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
            User::create([
                'uuid'         => Str::uuid(),
                'name'         => $row['name'],
                'email'        => $row['email'],
                'mobile_phone' => $row['mobile_phone'],
                'gender'       => $row['gender'],
                'password'     => null,
            ]);
            ++$this->rowCount;
        }
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email'],
            'mobile_phone' => ['required', 'max:255'],
            'gender'       => ['required', 'in:male,female'],
        ];
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
