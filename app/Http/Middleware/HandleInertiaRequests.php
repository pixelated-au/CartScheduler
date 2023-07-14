<?php

namespace App\Http\Middleware;

use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(private readonly GeneralSettings $settings)
    {
    }


    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'pagePermissions'   => $this->getPageAccessPermissions($request),
            'shiftAvailability' => [
                'timezone'       => config('app.timezone'),
                'duration'       => (int)config('cart-scheduler.shift_reservation_duration'),
                'period'         => config('cart-scheduler.shift_reservation_duration_period'),
                'releasedDaily'  => config('cart-scheduler.do_release_shifts_daily'),
                'weekDayRelease' => (int)config('cart-scheduler.release_weekly_shifts_on_day'),
            ],
        ]);
    }

    protected function getPageAccessPermissions(Request $request): array
    {
        $permissions = [];
        if (Gate::check('admin')) {
            $permissions['canAdmin'] = true;
            $user = $request->user();
            if (in_array($user->id, $this->settings->allowedSettingsUsers)) {
                $permissions['canEditSettings'] = true;
            }
        }

        return $permissions;
    }
}
