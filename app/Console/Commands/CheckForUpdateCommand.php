<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * @deprecated - replace with Streamline
 */
class CheckForUpdateCommand extends Command
{
    protected $signature = 'cart-scheduler:has-update {--beta : Check for beta version}';

    protected $description = 'Checks to see if there is an update available. If so, it will store the version number in the system settings.';

    public function __construct(private readonly UpdaterManager $updater, private readonly GeneralSettings $settings)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $wantsBeta = $this->option('beta');

        $this->info('Checking for updates...');
        // Check if new version is available
        $current = $this->updater->source()->getVersionInstalled();
        if (!$this->updater->source()->isNewVersionAvailable()) {
            $this->warn("No new updates available. Current version is $current");
            return;
        }
        $new = $this->updater->source()->getVersionAvailable();
        if (Str::endsWith($new, 'b')) {
            if (!$wantsBeta) {
                $this->warn("New version is a beta release. Ignoring. Current version is $current");

                return;
            }
            $this->warn("New version is a beta release, but --beta flag passed so continuing. Current version is $current");
        }

        // Get the current installed version
        $this->info("Current version: $current");

        // Get the new version available
        $this->info('New version: ' . $new);

        $this->settings->availableVersion = $new;
        $this->settings->save();
    }
}
