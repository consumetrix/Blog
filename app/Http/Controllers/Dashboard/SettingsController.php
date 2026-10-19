<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\BlogSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('dashboard.settings', ['underConstruction' => BlogSettings::underConstruction()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $was = BlogSettings::underConstruction();
        $now = $request->boolean('under_construction');

        BlogSettings::save(['under_construction' => $now], $request->user()->id);

        if ($was !== $now) {
            ActivityLog::record('settings.saved', $now ? 'Under construction turned on' : 'Under construction turned off');
        }

        return redirect()->route('dashboard.settings')->with('status', 'Settings saved.');
    }
}
