<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventLog;
use App\Models\App as ReverbApp;
use Illuminate\Http\Request;
use Pusher\Pusher;
use Throwable;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = EventLog::with('app.user')
            ->when($request->search, fn($q, $s) => $q->where(function ($sq) use ($s) {
                $sq->where('event_name', 'like', "%{$s}%")
                   ->orWhere('channel', 'like', "%{$s}%")
                   ->orWhere('uuid', 'like', "%{$s}%");
            }))
            ->when($request->app, fn($q, $id) => $q->where('app_id', $id))
            ->when($request->status, fn($q, $st) => $q->where('status', $st))
            ->when($request->date_from, fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $apps = ReverbApp::orderBy('name')->get();
        return view('admin.events.index', compact('events', 'apps'));
    }

    public function show(EventLog $event)
    {
        $event->load('app.user');
        return view('admin.events.show', compact('event'));
    }

    public function retry(EventLog $event)
    {
        if ($event->status !== 'failed') {
            return back()->with('error', 'Only failed events can be retried.');
        }

        try {
            $app = $event->app;
            $pusher = new Pusher(
                $app->app_key,
                $app->revealSecret(),
                $app->app_id,
                [
                    'host'   => config('reverb.apps.apps.0.options.host', config('reverb.servers.reverb.hostname')),
                    'port'   => (int) config('reverb.apps.apps.0.options.port', config('reverb.servers.reverb.port', 8080)),
                    'scheme' => config('reverb.apps.apps.0.options.scheme', 'http'),
                    'useTLS' => (bool) config('reverb.apps.apps.0.options.useTLS', false),
                ],
            );

            $pusher->trigger($event->channel, $event->event_name, $event->payload);

            $event->update([
                'status'       => 'delivered',
                'delivered_at' => now(),
                'retry_count'  => $event->retry_count + 1,
            ]);

            return back()->with('success', 'Event retried and delivered successfully.');
        } catch (Throwable $e) {
            $event->increment('retry_count');
            $event->update(['failure_reason' => $e->getMessage()]);
            return back()->with('error', 'Retry failed: ' . $e->getMessage());
        }
    }
}
