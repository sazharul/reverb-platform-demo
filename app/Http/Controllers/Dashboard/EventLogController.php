<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\App as ReverbApp;
use App\Models\EventLog;

class EventLogController extends Controller
{
    /**
     * The event log index is rendered by the Livewire EventLogTable component,
     * which handles its own filtering, pagination and polling.
     */
    public function index()
    {
        return view('dashboard.events.index');
    }

    public function show(EventLog $event)
    {
        $appIds = ReverbApp::where('user_id', auth()->id())->pluck('id');
        if (!$appIds->contains($event->app_id)) {
            abort(403);
        }

        $event->load('app');
        return view('dashboard.events.show', compact('event'));
    }
}


