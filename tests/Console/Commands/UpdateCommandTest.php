<?php

namespace Tests\Console\Commands;

use App\Console\Commands\UpdateCommand;
use App\Settings\GeneralSettings;
use Codedge\Updater\SourceRepositoryTypes\GithubRepositoryTypes\GithubTagType;
use DG\BypassFinals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Traits\LaravelUpdaterMocks;

class UpdateCommandTest extends TestCase
{
    use RefreshDatabase;
    use LaravelUpdaterMocks;

    public function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
        BypassFinals::setCacheDirectory(base_path('tests/.cache'));
        GeneralSettings::fake(['availableVersion' => '1.0.0']);
    }

    public function test_command_do_update(): void
    {
        $this->makeGithubTagTypeMock();

        $this->artisan(UpdateCommand::class)
            ->expectsOutput('Checking for updates...')
            ->expectsOutput('Current version: 1.0.0')
            ->expectsOutput('New version: 2.0.0')
            ->expectsOutput('Versions are not the same. Updating...')
            ->expectsOutput('Updating configuration to new version...')
            ->expectsOutput('DB Migration starting...')
            ->expectsOutput('DB Migration done')
            ->expectsOutput('Finished! Updated from 1.0.0 to 2.0.0')
            ->assertExitCode(0);
    }

    public function test_command_do_update_with_force(): void
    {
        $this->makeGithubTagTypeMock(availableVersion: '1.0.0');

        $this->artisan(UpdateCommand::class, ['--force' => true])
            ->expectsOutput('Checking for updates...')
            ->expectsOutput('No new updates available. Forcing an update...')
            ->expectsOutput('Current version: 1.0.0')
            ->expectsOutput('New version: 1.0.0')
            ->expectsOutput('Versions are the same. Forcing update...')
            ->expectsOutput('Updating configuration to new version...')
            ->expectsOutput('DB Migration starting...')
            ->expectsOutput('DB Migration done')
            ->expectsOutput('Finished! Updated from 1.0.0 to 1.0.0')
            ->assertExitCode(0);
    }

    public function test_command_do_update_with_beta(): void
    {
        $this->makeGithubTagTypeMock(availableVersion: '1.1.0b');

        $this->artisan(UpdateCommand::class)
            ->expectsOutput('New version is a beta release. Use --beta to force update to beta version 1.1.0b.')
            ->doesntExpectOutput('Versions are the same. Forcing update...')
            ->doesntExpectOutput('Versions are the same. Aborting.')
            ->assertExitCode(0);
    }

    public function test_command_do_update_with_beta_and_force(): void
    {
        $this->makeGithubTagTypeMock(availableVersion: '1.1.0b');

        $this->artisan(UpdateCommand::class, ['--beta' => true])
            ->expectsOutput('New version is a beta release. Forcing update to beta version 1.1.0b...')
            ->expectsOutput('Versions are not the same. Updating...')
            ->expectsOutput('Updating...')
            ->assertExitCode(0);
    }

    public function test_command_stop_execution_when_no_available_updates(): void
    {
        $this->makeGithubTagTypeMock(availableVersion: '1.0.0');

        $this->artisan(UpdateCommand::class)
            ->expectsOutput('No updates available. Use --force to force update.')
            ->doesntExpectOutput('Updating...')
            ->doesntExpectOutputToContain('Finished! Updated from')
            ->assertExitCode(0);
    }
}
