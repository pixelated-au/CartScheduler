<?php

namespace App\Providers;

use Codedge\Updater\UpdaterManager;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        AboutCommand::add('CartScheduler', static fn(UpdaterManager $updater) => [
            'Self Updater'  => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('bugsnag/bugsnag-laravel') : '<fg=yellow;options=bold>-</>',
            'Inertia'       => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('inertiajs/inertia-laravel') : '<fg=yellow;options=bold>-</>',
            'Excel Support' => class_exists(InstalledVersions::class) ? InstalledVersions::getPrettyVersion('maatwebsite/excel') : '<fg=yellow;options=bold>-</>',
            '<fg=bright-magenta>CartScheduler Version</>'
                            => '<fg=bright-magenta>' . $updater->source()->getVersionInstalled() . '</>',
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(!app()->isProduction());
        Schema::defaultStringLength(191);
        if (config('app.is_https')) {
            URL::forceScheme('https');
        }
    }
}
