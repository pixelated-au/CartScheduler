<?php

namespace Tests\Feature\App\Admin;

use App\Mail\UserAccountCreated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class UsersBatchImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_load_import_users_page(): void
    {
        $admin = User::factory()->enabled()->adminRoleUser()->create();
        $user  = User::factory()->enabled()->create();

        $this->actingAs($user)
            ->get("/admin/users/import")
            ->assertForbidden();

        $this->actingAs($admin)
            ->getJson("/admin/users/import")
            ->assertInertia(fn(AssertableInertia $page) => $page
                ->component('Admin/Users/Import')
            )
            ->assertOk();
    }

    public function test_admin_can_batch_import_three_users_and_users_receive_emails(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/users/import",
                ['file' => new UploadedFile(
                    path: base_path('tests/assets/test_admin_can_batch_import_three_users_and_users_receive_emails.xlsx'),
                    originalName: 'test_admin_can_batch_import_three_users_and_users_receive_emails.xlsx',
                    test: true,
                )],
                ['Content-Type' => 'multipart/form-data'],
            )
            ->assertRedirect();

        $this->assertDatabaseCount('users', 4);
        $uploaded = User::with('spouse')->where('id', '!=', $admin->getKey())->get();
        $this->assertEquals('Fee Smee', $uploaded[0]->name);
        $this->assertEquals('Lee Smee', $uploaded[1]->name);
        $this->assertEquals('Gee Smee', $uploaded[2]->name);
        $this->assertSame($uploaded[0]->getKey(), $uploaded[1]->spouse->getKey());
        $this->assertSame($uploaded[1]->getKey(), $uploaded[0]->spouse->getKey());
        $this->assertNull($uploaded[2]->spouse);

        Mail::assertSent(UserAccountCreated::class, 3);
    }

    public function test_admin_batch_import_handles_failed_validation_properly(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/users/import",
                ['file' => new UploadedFile(
                    path: base_path('tests/assets/test_admin_batch_import_fails_properly.xlsx'),
                    originalName: 'test_admin_batch_import_fails_properly.xlsx',
                    test: true,
                )],
                ['Content-Type' => 'multipart/form-data'],
            )
            ->assertUnprocessable()
            ->assertJsonFragment(['errors' => [
                ['There was an error on row 3. The year_of_birth must be at least 1924.'],
                ['There was an error on row 3. The selected serving_as is invalid.'],
                ['There was an error on row 3. The selected marital_status is invalid.'],
                ['There was an error on row 3. The responsible_brother field must be true or false.'],
                ['There was an error on row 3. The is_unrestricted field must be true or false.'],
            ]]);

        $this->assertDatabaseCount('users', 1);

        Mail::assertNothingSent();
    }

    public function test_admin_batch_import_handles_duplicate_users_properly(): void
    {
        $admin = User::factory()->adminRoleUser()->state(['is_enabled' => true])->create();
        // The next user will be duplicated in the import and should be updated
        User::factory()->female()->state(['id' => 2, 'email' => 'lee@example.com', 'is_enabled' => true])->create();

        Mail::fake();
        $this->actingAs($admin)
            ->postJson("/admin/users/import",
                ['file' => new UploadedFile(
                    path: base_path('tests/assets/test_admin_batch_import_handles_duplicate_users_properly.xlsx'),
                    originalName: 'test_admin_batch_import_handles_duplicate_users_properly.xlsx',
                    test: true,
                )],
                ['Content-Type' => 'multipart/form-data'],
            )
            ->assertRedirect();

        $uploaded = User::with('spouse')->where('id', '!=', $admin->getKey())->get();
        $this->assertDatabaseCount('users', 3);
        $this->assertSame('Existing User', $uploaded[0]->name);
        $this->assertSame('New User', $uploaded[1]->name);
        $this->assertSame($uploaded[0]->getKey(), $uploaded[1]->spouse->getKey());
        $this->assertSame($uploaded[1]->getKey(), $uploaded[0]->spouse->getKey());

        // Should only be one email sent because the duplicate user was updated
        Mail::assertSent(UserAccountCreated::class, 1);
    }
}
