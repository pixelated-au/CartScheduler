<?php

namespace App\Console\Commands;

use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;

class UpdateCommand extends Command
{
    protected $signature = 'update';

    protected              $description = 'CLI update';
    private UpdaterManager $updater;

    public function __construct(UpdaterManager $updater)
    {
        parent::__construct();

        $this->updater = $updater;
    }

    public function handle(): void
    {
        $this->info('Checking for updates...');
        // Check if new version is available
        if (!$this->updater->source()->isNewVersionAvailable()) {
            $this->info('No new version available');

            return;
        }

        // Get the current installed version
        $current = $this->updater->source()->getVersionInstalled();
        $this->info("Current version: $current");

        // Get the new version available
        $new = $this->updater->source()->getVersionAvailable();
        $this->info('New version: ' . $new);

        if (config('app.env') !== 'production') {
            $this->warn('Not in production, aborting update');

            return;
        }

        $this->info('Updating...');

        // Create a release
        $release = $this->updater->source()->fetch($new);

        // Run the update process
        $this->updater->source()->update($release);
        $this->info("Finished! Updated from $current to $new");
    }
}
