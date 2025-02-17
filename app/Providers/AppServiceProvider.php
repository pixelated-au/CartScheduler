<?php

namespace App\Providers;

use App\Actions\EncryptedErrorCodeAction;
use App\Http\Controllers\SetUserPasswordController;
use App\Interfaces\ObfuscatedErrorCode;
use Codedge\Updater\UpdaterManager;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }


    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());

        Schema::defaultStringLength(191);
        if (config('app.is_https')) {
            URL::forceScheme('https');
        }

        $this->configureSetPasswordController();

        AboutCommand::add('CartScheduler', static fn(UpdaterManager $updater) => [
//            'Self Updater'  => fn () => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('bugsnag/bugsnag-laravel') : '<fg=yellow;options=bold>-</>',
            'Inertia'       => fn () => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('inertiajs/inertia-laravel') : '<fg=yellow;options=bold>-</>',
            'Excel Support' => fn () => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('maatwebsite/excel') : '<fg=yellow;options=bold>-</>',
            '<fg=bright-magenta>CartScheduler Version</>'
                            => fn () => '<fg=bright-magenta>' . $updater->source()->getVersionInstalled() . '</>',
        ]);
    }

    public function configureSetPasswordController(): void
    {
        $this->app->when(SetUserPasswordController::class)
            ->needs(ObfuscatedErrorCode::class)
            ->give(
                fn(Application $application) => $application
                    ->make(EncryptedErrorCodeAction::class, ['message' => config('cart-scheduler.set_password_generic_error_message')])
            );
    }
}
