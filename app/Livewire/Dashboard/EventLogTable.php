<?php

namespace App\Livewire\Dashboard;

use App\Models\App as ReverbApp;
use App\Models\EventLog;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EventLogTable extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'app', except: '')]
    public string $appId = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    #[Url(as: 'date_from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'date_to', except: '')]
    public string $dateTo = '';

    /**
     * Reset pagination whenever any filter changes.
     */
    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'appId', 'status', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'appId', 'status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $user   = auth()->user();
        $appIds = ReverbApp::where('user_id', $user->id)->pluck('id');

        $events = EventLog::whereIn('app_id', $appIds)
            ->with('app')
            ->when($this->appId,   fn ($q) => $q->where('app_id', $this->appId))
            ->when($this->status,  fn ($q) => $q->where('status', $this->status))
            ->when($this->search,  fn ($q) => $q->where(function ($sq) {
                $sq->where('event_name', 'like', "%{$this->search}%")
                   ->orWhere('channel',  'like', "%{$this->search}%")
                   ->orWhere('uuid',     'like', "%{$this->search}%");
            }))
            ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo,   fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest()
            ->paginate(25);

        $apps = ReverbApp::where('user_id', $user->id)->orderBy('name')->get();

        return view('livewire.dashboard.event-log-table', compact('events', 'apps'));
    }
}

