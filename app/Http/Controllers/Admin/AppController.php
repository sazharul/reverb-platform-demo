<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App as ReverbApp;
use App\Reverb\ReverbAppManager;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index(Request $request)
    {
        $apps = ReverbApp::with('user')
            ->withCount(['eventLogs', 'channels'])
            ->when($request->search, fn($q, $s) => $q->where(function ($sq) use ($s) {
                $sq->where('name', 'like', "%{$s}%")
                   ->orWhere('app_id', 'like', "%{$s}%")
                   ->orWhere('app_key', 'like', "%{$s}%");
            }))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.apps.index', compact('apps'));
    }

    public function show(ReverbApp $app)
    {
        $app->load(['user', 'channels']);
        $app->loadCount(['eventLogs', 'channels']);
        $recentEvents = $app->eventLogs()->latest()->limit(10)->get();
        return view('admin.apps.show', compact('app', 'recentEvents'));
    }

    public function edit(ReverbApp $app)
    {
        return view('admin.apps.edit', compact('app'));
    }

    public function update(Request $request, ReverbApp $app)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:100',
            'max_connections'      => 'required|integer|min:1|max:100000',
            'is_active'            => 'nullable|boolean',
        ]);

        $app->update([
            'name'                 => $data['name'],
            'max_connections'      => $data['max_connections'],
            'is_active'            => $request->boolean('is_active'),
        ]);

        ReverbAppManager::clearCache($app);

        return redirect()->route('admin.apps.show', $app)->with('success', 'App updated.');
    }

    public function destroy(ReverbApp $app)
    {
        ReverbAppManager::clearCache($app);
        $app->delete();
        return redirect()->route('admin.apps.index')->with('success', 'App deleted.');
    }
}
