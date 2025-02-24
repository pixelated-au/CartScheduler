<?php

namespace Tests\Unit\Actions;

use App\Actions\SaveCurrentStreamlineVersionInSettings;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class SaveCurrentStreamlineVersionInSettingsTest extends TestCase
{

    public function test_should_update_settings_with_current_version_when_config_returns_valid_version(): void
    {
        /** @var GeneralSettings $mockSettings */
        $mockSettings = $this->mock(GeneralSettings::class,
            fn(MockInterface $mock) => $mock->shouldReceive('save')->once()
        );

        Config::shouldReceive('get')
            ->with('streamline.installed_version')
            ->andReturn('1.2.3');

        $action = new SaveCurrentStreamlineVersionInSettings($mockSettings);

        $action();

        $this->assertEquals('1.2.3', $mockSettings->currentVersion);
    }
}
