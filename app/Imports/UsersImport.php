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
                'uuid'                => Str::uuid(),
                'name'                => $row['name'],
                'email'               => $row['email'],
                'mobile_phone'        => $row['mobile_phone'],
                'gender'              => $row['gender'],
                'year_of_baptism'     => $row['year_of_baptism'],
                'appointment'         => $row['appointment'],
                'serving_as'          => $row['serving_as'],
                'marital_status'      => $row['marital_status'],
                'spouse_id'           => $row['spouse_id'],
                'responsible_brother' => $row['responsible_brother'],
                'password'            => null,
            ]);
            ++$this->rowCount;
        }
    }

    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'email', 'unique:users,email'],
            'mobile_phone'        => ['required', 'max:255'],
            'gender'              => ['required', 'in:male,female,m,f'],
            'year_of_baptism'     => ['nullable', 'integer', 'min:' . date('Y') - 100, 'max:' . date('Y')],
            'appointment'         => ['nullable', 'string', 'in:elder,ministerial servant'],
            'serving_as'          => ['nullable', 'string', 'in:field missionary,special pioneer,bethel family member,regular pioneer,publisher'],
            'marital_status'      => ['nullable', 'string', 'in:single,married,separated,divorced,widowed'],
            'spouse_email'        => ['nullable', 'email'],
            'spouse_id'           => ['nullable', 'exists:users,id'],
            'responsible_brother' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation(array $data, int $index)
    {
        $data['mobile_phone'] = Str::of($data['mobile_phone'])
            ->tap(fn(string $value) => Str::startsWith($value, '+') ? "0$value" : "$value")
            ->replaceMatches('/[^A-Za-z0-9]++/', '')
            ->trim()
            ->toString();

        $data['email'] = Str::of($data['email'])->lower()->trim()->toString();

        if ($data['gender'] === 'm') {
            $data['gender'] = 'male';
        }
        if ($data['gender'] === 'f') {
            $data['gender'] = 'female';
        }

        if ($data['spouse_email']) {
            $spouse = User::where('email', $data['spouse_email'])->first();
            if ($spouse) {
                $data['spouse_id'] = $spouse->id;
            }
        }

        if (!isset($data['year_of_baptism'])) {
            $data['year_of_baptism'] = null;
        }
        if (!isset($data['appointment'])) {
            $data['appointment'] = null;
        }
        if (!isset($data['serving_as'])) {
            $data['serving_as'] = null;
        }
        if (!isset($data['marital_status'])) {
            $data['marital_status'] = null;
        }
        if (!isset($data['spouse_id'])) {
            $data['spouse_id'] = null;
        }
        if (!isset($data['responsible_brother'])) {
            $data['responsible_brother'] = false;
        }

        return $data;
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
