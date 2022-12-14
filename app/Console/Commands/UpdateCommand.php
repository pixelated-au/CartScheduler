<?php

namespace App\Console\Commands;

use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;

class UpdateCommand extends Command
{
    protected $signature = 'update {--force : Force update}';

    protected              $description = 'CLI update';
    private UpdaterManager $updater;

    public function __construct(UpdaterManager $updater)
    {
        parent::__construct();

        $this->updater = $updater;
    }

    public function handle(): void
    {
        $doForce = $this->option('force');

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

        if (version_compare($new, $current, '>')) {
            $this->info('Versions are not the same. Updating...');

        } elseif ($doForce) {
            $this->info('Versions are the same. Forcing update...');

        } else {
            $this->warn('Versions are the same. Aborting.');

            return;
        }

        if (config('app.env') !== 'production') {
            $this->warn('Not in production, aborting update');

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
