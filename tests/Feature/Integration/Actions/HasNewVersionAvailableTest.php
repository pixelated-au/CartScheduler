<?php

namespace Tests\Feature\Integration\Actions;

use App\Actions\HasNewVersionAvailable;
use App\Settings\GeneralSettings;
use Tests\TestCase;

class HasNewVersionAvailableTest extends TestCase
{
    private HasNewVersionAvailable $hasNewVersionAvailable;
    private GeneralSettings $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->hasNewVersionAvailable = $this->app->make(HasNewVersionAvailable::class);
        $this->settings               = $this->app->make(GeneralSettings::class);
    }

    public function test_has_new_version_available_is_returning_correct_data(): void
    {
        $this->settings->currentVersion   = '1.0.0';
        $this->settings->availableVersion = '1.0.0';
        $this->settings->save();

        $this->assertFalse($this->hasNewVersionAvailable->execute());

        $this->settings->availableVersion = '1.0.1';
        $this->settings->save();

        $this->assertTrue($this->hasNewVersionAvailable->execute());

        $this->settings->currentVersion   = '';
        $this->settings->save();
        $this->assertFalse($this->hasNewVersionAvailable->execute());
        $this->settings->currentVersion   = '1.0.0';
        $this->settings->availableVersion = '';
        $this->settings->save();
        $this->assertFalse($this->hasNewVersionAvailable->execute());
    }
}
