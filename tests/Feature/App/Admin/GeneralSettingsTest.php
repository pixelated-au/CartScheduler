<?php

namespace App\Admin;

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class GeneralSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_edit_general_settings(): void
    {
        $admin = User::factory()->adminRoleUser()->create();

        $generalSettings = $this->app->make(GeneralSettings::class);

        $generalSettings->siteName                  = 'Old Site Name';
        $generalSettings->systemShiftStartHour      = 10;
        $generalSettings->systemShiftEndHour        = 13;
        $generalSettings->enableUserAvailability    = false;
        $generalSettings->enableUserLocationChoices = false;
        $generalSettings->save();

        $this->actingAs($admin)
            ->putJson("/admin/general-settings", [
                'siteName'                  => 'New Site Name',
                'systemShiftStartHour'      => 12,
                'systemShiftEndHour'        => 15,
                'enableUserAvailability'    => true,
                'enableUserLocationChoices' => true,
            ])
            ->assertRedirect(route('admin.settings'));

        $generalSettings->refresh();
        $this->assertSame('New Site Name', $generalSettings->siteName);
        $this->assertSame(12, $generalSettings->systemShiftStartHour);
        $this->assertSame(15, $generalSettings->systemShiftEndHour);
        $this->assertTrue($generalSettings->enableUserAvailability);
        $this->assertTrue($generalSettings->enableUserLocationChoices);
    }

    public function test_admin_can_update_allowed_settings_users(): void
    {
        $admin = User::factory()->adminRoleUser()->create();

        $generalSettings = $this->app->make(GeneralSettings::class);

        $generalSettings->allowedSettingsUsers = [1];
        $generalSettings->save();

        $this->actingAs($admin)
            ->putJson("/admin/allowed-settings-users", [
                'allowedSettingsUsers' => [1, 2],
            ])
            ->assertRedirect(route('admin.settings'));

        $generalSettings->refresh();
        $this->assertSame([1, 2], $generalSettings->allowedSettingsUsers);
    }

    public function test_admin_can_view_general_settings(): void
    {
        $admin = User::factory()->adminRoleUser()->create();

        $generalSettings = $this->app->make(GeneralSettings::class);

        $generalSettings->siteName                  = 'Old Site Name';
        $generalSettings->systemShiftStartHour      = 10;
        $generalSettings->systemShiftEndHour        = 13;
        $generalSettings->enableUserAvailability    = false;
        $generalSettings->enableUserLocationChoices = false;
        $generalSettings->currentVersion            = '1.0.0';
        $generalSettings->availableVersion          = '1.0.1';
        $generalSettings->allowedSettingsUsers      = [$admin->getKey()];
        $generalSettings->save();

        $this->actingAs($admin)
            ->get("/admin/settings")
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Settings/Show')
                ->has('settings', fn(AssertableInertia $data) => $data
                    ->where('siteName', 'Old Site Name')
                    ->where('systemShiftStartHour', 10)
                    ->where('systemShiftEndHour', 13)
                    ->where('enableUserAvailability', false)
                    ->where('enableUserLocationChoices', false)
                    ->where('currentVersion', '1.0.0')
                    ->where('availableVersion', '1.0.1')
                    ->where('allowedSettingsUsers', [$admin->getKey()])
                )
            );
    }

    public function test_non_allowed_admin_cannot_view_general_settings(): void
    {
        $admin  = User::factory()->adminRoleUser()->create();
        $admin2 = User::factory()->adminRoleUser()->create();

        $generalSettings = $this->app->make(GeneralSettings::class);

        $generalSettings->allowedSettingsUsers = [$admin2->getKey()];
        $generalSettings->save();

        $this->actingAs($admin)
            ->get("/admin/settings")
            ->assertNotFound();
    }

    public function test_admin_can_check_for_update(): void
    {
        $admin = User::factory()->adminRoleUser()->create();

        $generalSettings = $this->app->make(GeneralSettings::class);

        $generalSettings->currentVersion   = '1.0.0';
        $generalSettings->availableVersion = '1.0.1';
        $generalSettings->save();

        $this->actingAs($admin)
            ->getJson("/admin/check-update")
            ->assertOk()
            ->assertContent('1');

        $generalSettings->currentVersion = '1.0.1';
        $generalSettings->save();

        $this->actingAs($admin)
            ->getJson("/admin/check-update")
            ->assertOk()
            ->assertNoContent(200);
    }

    public function test_admin_can_check_for_beta_update(): void
    {
        $mock = $this->prepareArtisanMock();

        $admin = User::factory()->adminRoleUser()->create();
        $this->actingAs($admin)
            ->getJson("/admin/check-update")
            ->assertOk()
            ->assertNoContent(200);

        $mock::$versionInstalled = '2.0.0b';

        $this->actingAs($admin)
            ->getJson("/admin/check-update?beta=true")
            ->assertOk()
            ->assertContent('1');
    }

    public function test_run_system_update_with_no_available_update(): void
    {
        $this->prepareArtisanMock();
        $admin = User::factory()->adminRoleUser()->create();

        $this->actingAs($admin)
            ->postJson("/admin/do-update")
            ->assertOk()
            ->assertContent('success!');
    }

    public function test_run_system_update_with_available_update(): void
    {
        $mock = $this->prepareArtisanMock();
        $admin = User::factory()->adminRoleUser()->create();
        $mock::$versionInstalled = '2.0.0';

        $this->actingAs($admin)
            ->postJson("/admin/do-update")
            ->assertOk()
            ->assertContent('success!');
    }

    public function test_run_system_update_with_beta_available_update(): void
    {
        $mock = $this->prepareArtisanMock();
        $admin = User::factory()->adminRoleUser()->create();
        $mock::$versionInstalled = '2.0.0b';

        $this->actingAs($admin)
            ->postJson("/admin/do-update")
            ->assertOk()
            ->assertContent('success!');
    }

    private function prepareArtisanMock(): object
    {
        GeneralSettings::fake(['currentVersion'   => '1.0.0', 'availableVersion' => '1.0.0']);

        $mock = new class {
            public static string $versionInstalled = '1.0.0';

            public static function call(): void
            {
                app()->make(GeneralSettings::class)
                    ->fill(['availableVersion' => self::$versionInstalled])
                    ->save();
            }

            public static function output(): string
            {
                return 'success!';
            }
        };

        Artisan::swap($mock);
        return $mock;
    }
}
