<?php

namespace App\Console\Commands;

use App\Actions\SendShiftReminders;
use App\Console\Commands\Traits\ValidatesInput;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendUserShiftReminderCommand extends Command
{
    use ValidatesInput;

    protected $signature = 'cart-scheduler:shift-reminder
                              {userId? : The user to send the reminder to}
                              {--date= : The date of the shift. If not specified, it will use the system default}';
    protected $description = 'Send the users a reminder of an upcoming shift';

    public function handle(): void
    {
        if ($this->option('date'))
            SendShiftReminders::dispatch($this->argument('userId'), Carbon::parse($this->option('date')));
        else
            SendShiftReminders::dispatch($this->argument('userId'), $this->option('date'));
    }

    protected function rules(): array
    {
        return [
            'userId' => ['nullable', 'int'],
            'date'   => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
