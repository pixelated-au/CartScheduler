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
        // Check if new version is available
        if (!$this->updater->source()->isNewVersionAvailable()) {
            $this->info('No new version available');

            return;
        }
        // Get the current installed version
        $this->info('Current version: ' . $this->updater->source()->getVersionInstalled());

        // Get the new version available
        $versionAvailable = $this->updater->source()->getVersionAvailable();
        $this->info('New version: ' . $versionAvailable);

        if (config('app.env') !== 'production') {
            $this->warn('Not in production, aborting update');

            return;
        }

        $this->info('Updating...');

        // Create a release
        $release = $this->updater->source()->fetch($versionAvailable);

        // Run the update process
        $this->updater->source()->update($release);
    }
}
