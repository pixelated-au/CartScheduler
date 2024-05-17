<?php

namespace Tests\Console\Commands;

use App\Console\Commands\CheckForUpdateCommand;
use App\Settings\GeneralSettings;
use Codedge\Updater\SourceRepositoryTypes\GithubRepositoryTypes\GithubTagType;
use DG\BypassFinals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class CheckForUpdateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        BypassFinals::enable();
        BypassFinals::setCacheDirectory(base_path('tests/.cache'));
        GeneralSettings::fake(['availableVersion' => '1.0.0']);
    }

    public function test_command_updates_available(): void
    {
        $this->partialMock(GithubTagType::class, fn(MockInterface $mock) => $mock
            ->shouldReceive('getVersionInstalled')
            ->andReturn('1.0.0')
            ->shouldReceive('isNewVersionAvailable')
            ->andReturnTrue()
            ->shouldReceive('getVersionAvailable')
            ->andReturn('2.0.0')
        );
        $this->artisan(CheckForUpdateCommand::class)
            ->expectsOutput('Checking for updates...')
            ->expectsOutput('Current version: 1.0.0')
            ->expectsOutput('New version: 2.0.0')
            ->assertExitCode(0);
    }

    public function test_command_updates_not_available(): void
    {
        $this->partialMock(GithubTagType::class, fn(MockInterface $mock) => $mock
            ->shouldReceive('getVersionInstalled')
            ->andReturn('1.0.0')
            ->shouldReceive('isNewVersionAvailable')
            ->andReturnFalse()
        );
        $this->artisan(CheckForUpdateCommand::class)
            ->expectsOutput('No new updates available. Current version is 1.0.0')
            ->doesntExpectOutputToContain('New version')
            ->assertExitCode(0);
    }

    public function test_command_beta_updates_available_without_flag(): void
    {
        $this->partialMock(GithubTagType::class, fn(MockInterface $mock) => $mock
            ->shouldReceive('getVersionInstalled')
            ->andReturn('1.0.0')
            ->shouldReceive('isNewVersionAvailable')
            ->andReturnTrue()
            ->shouldReceive('getVersionAvailable')
            ->andReturn('1.1.0b')
        );
        $this->artisan(CheckForUpdateCommand::class, ['--beta' => true])
            ->expectsOutput('New version is a beta release, but --beta flag passed so continuing. Current version is 1.0.0')
            ->assertExitCode(0);
    }

    public function test_command_beta_updates_not_available_with_beta_flag(): void
    {
        $this->partialMock(GithubTagType::class, fn(MockInterface $mock) => $mock
            ->shouldReceive('getVersionInstalled')
            ->andReturn('1.0.0')
            ->shouldReceive('isNewVersionAvailable')
            ->andReturnTrue()
            ->shouldReceive('getVersionAvailable')
            ->andReturn('1.1.0b')
        );
        $this->artisan(CheckForUpdateCommand::class)
            ->expectsOutput('New version is a beta release. Ignoring. Current version is 1.0.0')
            ->assertExitCode(0);
    }
}
