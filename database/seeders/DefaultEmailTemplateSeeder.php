<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('email_templates')->insert(
            array(
                'name' => 'reminder_email',
                'content' => "### {{ app_name }} Upcoming Shift

Dear {{ user_name }},
this is a reminder that you have
upcoming shift(s) scheduled for **{{ relative_date }}** ({{ full_date }}):

{{ shift_info }}

Thank you,<br>
The {{ app_name }} Team"
            )
        );
    }
}
