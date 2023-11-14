<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateCommand extends Command
{
    protected $signature = 'cart-scheduler:do-update {--force : Force update}';

    protected $description = 'CLI update';

    public function __construct(
        private readonly UpdaterManager  $updater,
        private readonly GeneralSettings $settings,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $doForce = $this->option('force');
        $beta    = $this->option('beta');

        $this->info('Checking for updates...');
        // Check if new version is available
        if (!$this->updater->source()->isNewVersionAvailable()) {
            if ($doForce) {
                $this->warn('No new updates available. Forcing an update...');

            } else {
                $this->warn('No updates available. Use --force to force update.');

                return;
            }

        }

        // Get the current installed version
        $current = $this->updater->source()->getVersionInstalled();
        $this->info("Current version: $current");

        // Get the new version available
        $new = $this->updater->source()->getVersionAvailable();
        $this->info('New version: ' . $new);

        if (Str::endsWith($new, 'b')) {
            if (!$beta) {
                $this->error("New version is a beta release. Use --beta to force update to beta version $new.");
                return;
            }
            $this->warn("New version is a beta release. Forcing update to beta version $new...");
        }


        if (version_compare($new, $current, '>')) {
            $this->info('Versions are not the same. Updating...');

        } elseif ($doForce) {
            $this->info('Versions are the same. Forcing update...');

        } else {
            // Shouldn't happen but theoretically possible...
            $this->error('Versions are the same. Aborting.');
            return;
        }

        if (config('app.env') === 'local') {
            $this->warn('Not in production, pretending to update...');
            $this->settings->currentVersion = $new;
            $this->settings->save();

            return;
        }

        $this->info('Updating...');

        // Create a release
        $release = $this->updater->source()->fetch($new);

        // Run the update process
        $this->updater->source()->update($release);

        $this->info('Updating configuration to new version...');
        $this->setEnv($current, $new);
        $this->call('config:cache'); // Clear config cache

        $this->info('DB Migration starting...');
        $this->call('migrate', ['--force' => true]); // Run migrations
        $this->info('DB Migration done');
        $this->call('optimize:clear'); // Clear cache

        $this->settings->currentVersion = $new;
        $this->settings->save();
        $this->info("Finished! Updated from $current to $new");
    }

    private function setEnv(string $old, string $new): void
    {
        $key = 'SELF_UPDATER_VERSION_INSTALLED';
        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . $old,
            $key . '=' . $new,
            file_get_contents(app()->environmentFilePath())
        ));
    }
}
