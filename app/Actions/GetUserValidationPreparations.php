<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Str;

class GetUserValidationPreparations
{
    public function execute(array $data): array
    {
        $data['mobile_phone'] = isset($data['mobile_phone'])
            ? Str::of($data['mobile_phone'])
                ->tap(fn(string $value) => Str::startsWith($value, '+') ? "0$value" : "$value")
                ->replaceMatches('/[^A-Za-z0-9]++/', '')
                ->trim()
                ->toString()
            : null;

        $data['email'] = isset($data['email'])
            ? Str::of($data['email'])->lower()->trim()->toString()
            : null;

        if (isset($data['gender']) && $data['gender'] === 'm') {
            $data['gender'] = 'male';
        }
        if (isset($data['gender']) && $data['gender'] === 'f') {
            $data['gender'] = 'female';
        }

        if (isset($data['spouse_email'])) {
            $spouse = User::where('email', $data['spouse_email'])->first();
            if ($spouse) {
                $data['spouse_id'] = $spouse->id;
            }
        }

        if (!isset($data['year_of_birth'])) {
            $data['year_of_birth'] = null;
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
}
