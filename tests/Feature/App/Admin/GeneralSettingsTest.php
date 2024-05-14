<?php

namespace App\Admin;

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $generalSettings->save();

        $this->actingAs($admin)
            ->getJson("/admin/settings")
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Settings/Show')
                ->has('settings', fn(AssertableInertia $data) => $data
                    ->where('siteName', 'Old Site Name')
                    ->where('systemShiftStartHour', 10)
                    ->where('systemShiftEndHour', 13)
                    ->where('enableUserAvailability', false)
                    ->where('enableUserLocationChoices', false)
                    ->etc()
                )
            );
    }
}
