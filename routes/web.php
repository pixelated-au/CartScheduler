<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AvailableShiftsForMonthController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\MissingReportsForUserController;
use App\Http\Controllers\SaveShiftReportController;
use App\Http\Controllers\SetUserPasswordController;
use App\Http\Controllers\ToggleShiftReservationController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', static function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});*/

Route::get('/', static fn() => Inertia::render('Auth/Login'));

Route::get('/set-password/{user}/{hashedEmail}', [SetUserPasswordController::class, 'show'])->name('set.password.show');
Route::post('/set-password', [SetUserPasswordController::class, 'update'])->name('set.password.update');

Route::get('/mail', static fn() => new App\Mail\UserAccountCreated(App\Models\User::find(1)));

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/', static fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/shifts', AvailableShiftsForMonthController::class);
    Route::get('/outstanding-reports', MissingReportsForUserController::class);
    Route::post('/reserve-shift', ToggleShiftReservationController::class);
    Route::post('/save-report', SaveShiftReportController::class)->name('save.report');

    Route::prefix('admin')->group(static function () {
        Route::get('/', AdminDashboardController::class)->name('admin.dashboard');
        Route::resource('/users', UsersController::class)->names([
            'index'   => 'admin.users.index',
            'create'  => 'admin.users.create',
            'store'   => 'admin.users.store',
            'show'    => 'admin.users.show',
            'edit'    => 'admin.users.edit',
            'update'  => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);

        Route::resource('/locations', LocationsController::class)->names([
            'index'   => 'admin.locations.index',
            'create'  => 'admin.locations.create',
            'store'   => 'admin.locations.store',
            'show'    => 'admin.locations.show',
            'edit'    => 'admin.locations.edit',
            'update'  => 'admin.locations.update',
            'destroy' => 'admin.locations.destroy',
        ]);

        //Route::get('/', static fn() => Inertia::render('Admin/Dashboard'))->name('admin.dashboard');

        //Route::resource('shifts', 'Admin\ShiftsController');
        //Route::resource('locations', 'Admin\LocationsController');
        //Route::resource('users', 'Admin\UsersController');
        //Route::resource('roles', 'Admin\RolesController');
        //Route::resource('permissions', 'Admin\PermissionsController');
        //Route::resource('reports', 'Admin\ReportsController');
        //Route::resource('reservations', 'Admin\ReservationsController');
    });
});
