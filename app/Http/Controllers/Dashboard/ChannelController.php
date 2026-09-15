<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\App as ReverbApp;
use App\Models\Channel;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $appIds = ReverbApp::where('user_id', $user->id)->pluck('id');

        $channels = Channel::whereIn('app_id', $appIds)
            ->with('app')
            ->when($request->app, fn($q, $appId) => $q->where('app_id', $appId))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $apps = ReverbApp::where('user_id', $user->id)->orderBy('name')->get();

        return view('dashboard.channels.index', compact('channels', 'apps'));
    }

    public function create()
    {
        $apps = ReverbApp::where('user_id', auth()->id())->where('is_active', true)->orderBy('name')->get();
        return view('dashboard.channels.create', compact('apps'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'app_id' => 'required|exists:apps,id',
            'name'   => 'required|string|max:200',
            'type'   => 'required|in:public,private,presence',
        ]);

        $app = ReverbApp::where('id', $data['app_id'])->where('user_id', auth()->id())->firstOrFail();

        $limiter = app(PlanLimitService::class);
        if (!$limiter->canCreateChannel(auth()->user(), $app->id)) {
            return back()->with('error', 'Channel limit reached for your current plan. Please upgrade.');
        }

        $channelName = $data['name'];
        if ($data['type'] === 'private' && !str_starts_with($channelName, 'private-')) {
            $channelName = 'private-' . $channelName;
        } elseif ($data['type'] === 'presence' && !str_starts_with($channelName, 'presence-')) {
            $channelName = 'presence-' . $channelName;
        }

        Channel::create([
            'app_id'    => $app->id,
            'name'      => $channelName,
            'type'      => $data['type'],
            'is_active' => true,
        ]);

        return redirect()->route('user.channels.index')->with('success', 'Channel created successfully.');
    }

    public function edit(Channel $channel)
    {
        $this->authorizeChannel($channel);
        return view('dashboard.channels.edit', compact('channel'));
    }

    public function update(Request $request, Channel $channel)
    {
        $this->authorizeChannel($channel);

        $data = $request->validate([
            'name'      => 'required|string|max:200',
            'type'      => 'required|in:public,private,presence',
            'is_active' => 'nullable|boolean',
        ]);

        $channel->update([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('user.channels.index')->with('success', 'Channel updated.');
    }

    public function destroy(Channel $channel)
    {
        $this->authorizeChannel($channel);
        $channel->delete();
        return redirect()->route('user.channels.index')->with('success', 'Channel deleted.');
    }

    private function authorizeChannel(Channel $channel): void
    {
        $appIds = ReverbApp::where('user_id', auth()->id())->pluck('id');
        if (!$appIds->contains($channel->app_id)) {
            abort(403);
        }
    }
}
