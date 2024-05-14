<?php
/**
 * Project: ${PROJECT_NAME}
 * Owner: Pixelated
 * Copyright: 2022
 */

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DeleteShiftsController extends Controller
{
    public function __invoke(Shift $shift)
    {
        if ($shift->delete()) {
            return response('', Response::HTTP_NO_CONTENT);
        }

        return response('Shift could not be deleted! Try again or please contact developer.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
