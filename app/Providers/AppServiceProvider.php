<?php

namespace App\Providers;

use App\Actions\EncryptedErrorCodeAction;
use App\Http\Controllers\SetUserPasswordController;
use App\Interfaces\ObfuscatedErrorCode;
use App\Listeners\StreamlineInstalledVersionSetListener;
use App\Listeners\StreamlineNextAvailableVersionUpdatedListener;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Pixelated\Streamline\Events\InstalledVersionSet;
use Pixelated\Streamline\Events\NextAvailableVersionUpdated;

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

        Event::listen(InstalledVersionSet::class, StreamlineInstalledVersionSetListener::class);
        Event::listen(NextAvailableVersionUpdated::class, StreamlineNextAvailableVersionUpdatedListener::class);

        $this->configureSetPasswordController();
    }

    public function configureSetPasswordController(): void
    {
        $this->app->when(SetUserPasswordController::class)
            ->needs(ObfuscatedErrorCode::class)
            ->give(
                fn(Application $application) => $application
                    ->make(EncryptedErrorCodeAction::class,
                        ['message' => config('cart-scheduler.set_password_generic_error_message')])
            );
    }
}
