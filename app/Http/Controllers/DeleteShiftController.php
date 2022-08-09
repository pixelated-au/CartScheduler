<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use RuntimeException;

class DeleteShiftController extends Controller
{
    public function __invoke(Shift $shift)
    {
        if ($shift->delete()) {
            return response('', 204);
        }
        throw new RuntimeException('Could not delete shift');
    }
}
