<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name'                  => SystemSetting::get('site_name', config('app.name')),
            'registration_enabled'       => SystemSetting::get('registration_enabled', '1'),
            'default_max_connections'    => SystemSetting::get('default_max_connections', '200'),
            'default_daily_message_limit'=> SystemSetting::get('default_daily_message_limit', '10000'),
            'maintenance_mode'           => SystemSetting::get('maintenance_mode', '0'),
            'event_log_retention_days'   => SystemSetting::get('event_log_retention_days', '90'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name'                   => 'required|string|max:100',
            'registration_enabled'        => 'required|in:0,1',
            'default_max_connections'     => 'required|integer|min:1',
            'default_daily_message_limit' => 'required|integer|min:1',
            'maintenance_mode'            => 'required|in:0,1',
            'event_log_retention_days'    => 'required|integer|min:1|max:365',
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::set($key, $value, 'general');
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
