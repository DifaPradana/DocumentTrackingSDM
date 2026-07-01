<?php

use Carbon\Carbon;
use App\Models\Calendar;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function upcomingEvents(): array
    {
        $today = Carbon::today();

        $rows = Calendar::query()
            ->where('user_id', auth()->id())
            ->whereDate('end_date', '>=', $today)
            ->orderBy('start_date')
            ->orderBy('start_time')
            ->get();

        return $rows->map(function ($row) use ($today) {
            $start = Carbon::parse($row->start_date);
            $end = Carbon::parse($row->end_date);

            return [
                'id' => $row->calendar_id,
                'title' => $row->event_title,
                'color' => $row->color,
                'is_all_day' => (bool) $row->is_all_day,
                'is_today' => $start->isSameDay($today),
                'is_tomorrow' => $start->isSameDay($today->copy()->addDay()),
                'is_done' => (bool) $row->is_done,
                'date_label' => $start->translatedFormat('D, j M Y'),
                'multi_day' => ! $start->isSameDay($end),
                'end_date_label' => $end->translatedFormat('D, j M Y'),
                'time_label' => (! $row->is_all_day && $row->start_time)
                    ? Carbon::parse($row->start_time)->format('H:i') . ' - ' . Carbon::parse($row->end_time)->format('H:i')
                    : null,
            ];
        })->toArray();
    }

    public function toggleDone(int $id): void
    {
        $row = Calendar::where('user_id', auth()->id())
            ->where('calendar_id', $id)
            ->first();

        if ($row) {
            $row->update(['is_done' => ! $row->is_done]);
            unset($this->upcomingEvents);
        }

        $this->dispatch('upcoming-events-updated');
    }

    public function deleteEvent(int $id): void
    {
        Calendar::where('user_id', auth()->id())
            ->where('calendar_id', $id)
            ->delete();

        unset($this->upcomingEvents);

        $this->dispatch('upcoming-events-updated');
    }
};
?>

<div class="card h-100">
    <div class="card-body p-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h5 class="mb-0 fw-semibold">Upcoming Events</h5>
            <a href="{{ route('public.calendar.page') }}" class="fc-see-all">Lihat semua</a>
        </div>

        @if (count($this->upcomingEvents) === 0)
        <div class="fc-upcoming-empty">
            <i class="ti ti-calendar-off" aria-hidden="true"></i>
            <p>Tidak ada event mendatang.</p>
        </div>
        @else
        <div class="fc-upcoming-scroll">
            <div class="fc-upcoming-list">
                @foreach ($this->upcomingEvents as $ev)
                <div class="fc-upcoming-item {{ $ev['color'] }} {{ $ev['is_done'] ? 'is-done' : '' }}" wire:key="upcoming-{{ $ev['id'] }}">
                    <div class="fc-upcoming-content">
                        <div class="fc-upcoming-top">
                            <div class="fc-upcoming-title">
                                @if ($ev['is_done'])
                                <i class="ti ti-circle-check-filled fc-upcoming-check" aria-hidden="true"></i>
                                @endif
                                {{ $ev['title'] }}
                            </div>

                            @if ($ev['is_done'])
                            <span class="fc-upcoming-badge done">Selesai</span>
                            @elseif ($ev['is_today'])
                            <span class="fc-upcoming-badge today">Hari ini</span>
                            @elseif ($ev['is_tomorrow'])
                            <span class="fc-upcoming-badge tomorrow">Besok</span>
                            @endif
                        </div>

                        <div class="fc-upcoming-meta">
                            @if (! $ev['is_today'] && ! $ev['is_tomorrow'])
                            <span class="fc-upcoming-meta-item">
                                <i class="ti ti-calendar" aria-hidden="true"></i>
                                {{ $ev['date_label'] }}
                            </span>
                            @endif

                            @if ($ev['multi_day'])
                            <span class="fc-upcoming-meta-item">&ndash; {{ $ev['end_date_label'] }}</span>
                            @endif

                            <span class="fc-upcoming-meta-item">
                                <i class="ti ti-clock" aria-hidden="true"></i>
                                {{ $ev['time_label'] ?? 'All day' }}
                            </span>
                        </div>
                    </div>
                    @if ($ev['is_done'] == false)
                    <div class="fc-upcoming-actions">
                        <button
                            type="button"
                            class="fc-upcoming-action-btn success {{ $ev['is_done'] ? 'active' : '' }}"
                            wire:click="toggleDone({{ $ev['id'] }})"
                            title="{{ $ev['is_done'] ? 'Batal selesai' : 'Tandai selesai' }}">
                            <i class="ti ti-check" aria-hidden="true"></i>
                        </button>
                        <a
                            href="{{ route('public.calendar.page', ['event' => $ev['id']]) }}"
                            class="fc-upcoming-action-btn warning"
                            title="Edit event">
                            <i class="ti ti-pencil" aria-hidden="true"></i>
                        </a>
                        <button
                            type="button"
                            class="fc-upcoming-action-btn danger"
                            wire:click="deleteEvent({{ $ev['id'] }})"
                            wire:confirm="Yakin ingin menghapus event '{{ $ev['title'] }}'?"
                            title="Hapus event">
                            <i class="ti ti-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('upcoming-events-updated', () => {
            // tunggu sebentar biar DOM selesai di-morph Livewire dulu
            setTimeout(() => {
                document.querySelectorAll('[data-simplebar]').forEach((el) => {
                    if (el.SimpleBar) {
                        el.SimpleBar.recalculate();
                    }
                });
            }, 50);
        });
    });
</script>