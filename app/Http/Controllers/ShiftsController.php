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

class ShiftsController extends Controller
{
    public function index()
    {
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
    }

    public function show(Shift $shift)
    {
    }

    public function edit(Shift $shift)
    {
    }

    public function update(Request $request, Shift $shift)
    {
    }

    public function destroy(Shift $shift)
    {
        if ($shift->delete()) {
            return response('', Response::HTTP_NO_CONTENT);
        }

        return response('Shift could not be deleted! Please contact developer.', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
