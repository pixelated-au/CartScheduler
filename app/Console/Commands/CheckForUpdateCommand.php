<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;

class CheckForUpdateCommand extends Command
{
    protected $signature = 'cart-scheduler:has-update';

    protected $description = 'Checks to see if there is an update available. If so, it will store the version number in the system settings.';

    public function __construct(private readonly UpdaterManager $updater, private readonly GeneralSettings $settings)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('Checking for updates...');
        // Check if new version is available
        $current = $this->updater->source()->getVersionInstalled();
        if (!$this->updater->source()->isNewVersionAvailable()) {
            $this->warn("No new updates available. Current version is $current");
            return;
        }

        // Get the current installed version
        $this->info("Current version: $current");

        // Get the new version available
        $new = $this->updater->source()->getVersionAvailable();
        $this->info('New version: ' . $new);

        $this->settings->availableVersion = $new;
        $this->settings->save();
    }
}
