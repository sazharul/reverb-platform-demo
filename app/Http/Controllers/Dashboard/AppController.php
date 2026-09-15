<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\App as ReverbApp;
use App\Models\Channel;
use App\Reverb\ReverbAppManager;
use App\Http\Controllers\Controller;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppController extends Controller
{
    public function index()
    {
        $apps = $this->userAppsQuery()
            ->withCount(['eventLogs', 'channels'])
            ->latest()
            ->paginate(10);

        return view('dashboard.apps.index', compact('apps'));
    }

    public function create()
    {
        return view('dashboard.apps.create');
    }

    public function store(Request $request)
    {
        // Plan limit check
        $limiter = app(PlanLimitService::class);
        if (!$limiter->canCreateApp(auth()->user())) {
            return back()->with('error', 'App limit reached for your current plan. Please upgrade.');
        }

        $data = $request->validate([
            'name'             => 'required|string|max:100',
            'description'      => 'nullable|string',
            'allowed_origins'  => 'nullable|string',
            'max_connections'  => 'nullable|integer|min:1|max:100000',
            'webhook_url'      => 'nullable|url|max:500',
            'channels'         => 'nullable|array|max:20',
            'channels.*.name'  => 'required_with:channels|string|max:200',
            'channels.*.type'  => 'required_with:channels|in:public,private,presence',
        ]);

        // Cap max_connections to plan limit
        $planMaxConn = $limiter->maxConnectionsForApp(auth()->user());
        $maxConn = min($data['max_connections'] ?? 200, $planMaxConn);

        $app = ReverbApp::create([
            'user_id'              => auth()->id(),
            'name'                 => $data['name'],
            'description'          => $data['description'] ?? null,
            'allowed_origins'      => $this->parseAllowedOrigins($data['allowed_origins'] ?? null),
            'max_connections'      => $maxConn,
            'default_channel_type' => 'public', // backward compat default
            'webhook_url'          => $data['webhook_url'] ?? null,
            'app_id'               => Str::random(12),
            'app_key'              => Str::random(32),
            'app_secret'           => Str::random(64),
            'is_active'            => true,
        ]);

        // Create initial channels if provided
        if (!empty($data['channels'])) {
            $channelLimit = $limiter->getPlan(auth()->user())->max_channels_per_app;
            $created = 0;

            foreach ($data['channels'] as $ch) {
                if ($created >= $channelLimit) break;

                $channelName = $ch['name'];
                if ($ch['type'] === 'private' && !str_starts_with($channelName, 'private-')) {
                    $channelName = 'private-' . $channelName;
                } elseif ($ch['type'] === 'presence' && !str_starts_with($channelName, 'presence-')) {
                    $channelName = 'presence-' . $channelName;
                }

                Channel::create([
                    'app_id'    => $app->id,
                    'name'      => $channelName,
                    'type'      => $ch['type'],
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        return redirect()
            ->route('user.apps.show', $app)
            ->with('success', 'App created successfully. Save your credentials in a secure place.');
    }

    public function show(ReverbApp $app)
    {
        $reverbApp = $this->resolveUserApp($app);
        $reverbApp->load('channels');

        return view('dashboard.apps.show', compact('reverbApp'));
    }

    public function edit(ReverbApp $app)
    {
        $reverbApp = $this->resolveUserApp($app);

        return view('dashboard.apps.edit', compact('reverbApp'));
    }

    public function update(Request $request, ReverbApp $app)
    {
        $app = $this->resolveUserApp($app);

        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'description'     => 'nullable|string',
            'allowed_origins' => 'nullable|string',
            'max_connections' => 'required|integer|min:1|max:100000',
            'webhook_url'     => 'nullable|url|max:500',
            'is_active'       => 'nullable|boolean',
        ]);

        $app->update([
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'allowed_origins' => $this->parseAllowedOrigins($data['allowed_origins'] ?? null),
            'max_connections' => $data['max_connections'],
            'webhook_url'     => $data['webhook_url'] ?? null,
            'is_active'       => $request->boolean('is_active'),
        ]);

        $this->clearReverbAppCache($app);

        return redirect()
            ->route('user.apps.show', $app)
            ->with('success', 'App updated successfully.');
    }

    public function destroy(ReverbApp $app)
    {
        $app = $this->resolveUserApp($app);

        $this->clearReverbAppCache($app);
        $app->delete();

        return redirect()
            ->route('user.apps.index')
            ->with('success', 'App deleted successfully.');
    }

    private function userAppsQuery()
    {
        return ReverbApp::query()->where('user_id', auth()->id());
    }

    private function resolveUserApp(ReverbApp $app): ReverbApp
    {
        return $this->userAppsQuery()->findOrFail($app->id);
    }

    private function parseAllowedOrigins(?string $allowedOrigins): ?array
    {
        if (!$allowedOrigins) {
            return null;
        }

        $origins = collect(preg_split('/[\r\n,]+/', $allowedOrigins))
            ->map(static fn ($origin) => trim((string) $origin))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($origins) ? null : $origins;
    }

    private function clearReverbAppCache(ReverbApp $app): void
    {
        ReverbAppManager::clearCache($app);
    }
}
