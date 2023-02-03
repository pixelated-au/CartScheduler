<?php

namespace Tests\Feature\App\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * For email testing:
 *
 * @see https://laravel.com/docs/9.x/mail#testing-mailables
 * @see https://laravel.com/docs/9.x/mocking#mail-fake
 */
class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_new_user_and_user_receives_email(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_edit_user(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_delete_user(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_send_password_reset_email(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }

    public function test_admin_can_batch_import_three_users_and_users_receive_emails(): void
    {
        $this->markTestSkipped('Not implemented yet.');
    }
}
