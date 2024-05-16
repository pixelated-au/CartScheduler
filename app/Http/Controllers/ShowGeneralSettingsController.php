<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShowGeneralSettingsController extends Controller
{
    public function __construct(private readonly GeneralSettings $settings)
    {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function __invoke(Request $request)
    {
        if (!in_array($request->user()->getKey(), $this->settings->allowedSettingsUsers, true)) {
            abort(404);
        }

        return Inertia::render('Admin/Settings/Show', [
            'settings' => $this->settings->toArray(),
        ]);
    }
}
