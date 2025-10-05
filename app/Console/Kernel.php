<?php
namespace App\Console;

use Bugsnag\BugsnagLaravel\OomBootstrapper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function bootstrappers(): array
    {
        return array_merge(
            [OomBootstrapper::class],
            parent::bootstrappers(),
        );
    }

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        /** @noinspection SpellCheckingInspection */
        $schedule->command('activitylog:clean --force')->daily();
        $schedule->command('cart-scheduler:has-update')->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
