<?php

namespace App\Http\Controllers;

use App\Actions\GetEmailReminderTemplate;
use App\Http\Requests\UpdateEmailSettingsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class EmailSettingsController extends Controller
{

    public function __invoke(Request $request)
    {
        return Inertia::render('Admin/EmailSettings/Edit', [
            'emailReminderText' => GetEmailReminderTemplate::GetReminderTemplate()->content,
        ]);
    }

    public function edit(UpdateEmailSettingsRequest $request): RedirectResponse
    {
        DB::beginTransaction();
        $new_content = $request->input('emailReminderText');
        Log::debug("got text:", [ "new_content" => $new_content ]);
        $template = GetEmailReminderTemplate::GetReminderTemplate();
        $template->content = $new_content;
        $template->save();
        DB::commit();

        return Redirect::route('admin.emailsettings');
    }
}

