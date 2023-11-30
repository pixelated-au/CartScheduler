<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class UsersImportController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(): Response
    {
        $this->authorize('import', User::class);

        return Inertia::render('Admin/Users/Import', [
            'templateFile' => asset('storage/example-user-import.xlsx'),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,xlsx,xls'],
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        $rowCount = $import->getRowCount();
        session()->flash('flash.banner', "$rowCount Users were imported!");
        session()->flash('flash.bannerStyle', 'success');

        return Redirect::route('admin.users.import.show');
    }
}
