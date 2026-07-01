<?php

use Carbon\Carbon;
use App\Models\Calendar;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    // ===== State =====
    public string $cursor;
    public string $view = 'month';

    // Modal / form state
    public ?int $editingId = null;
    public ?string $pendingDate = null;
    public string $eventTitle = '';
    public string $eventDescription = '';
    public string $eventStart = '';
    public string $eventEnd = '';
    public string $eventStartTime = '';
    public string $eventEndTime = '';
    public bool $isAllDay = false;
    public string $selectedColor = 'violet';
    public bool $isViewMode = false;
    public bool $isDone = false;
    public int $jumpMonth;
    public int $jumpYear;

    protected function colors(): array
    {
        return ['violet', 'teal', 'pink', 'blue', 'amber'];
    }

    protected function hourLabels(): array
    {
        return ['12am', '1am', '2am', '3am', '4am', '5am', '6am', '7am', '8am', '9am', '10am', '11am', '12pm', '1pm', '2pm', '3pm', '4pm', '5pm', '6pm', '7pm', '8pm', '9pm', '10pm', '11pm'];
    }

    public function mount(): void
    {
        $c = $this->today();
        $this->cursor = $c->toDateString();
        $this->jumpMonth = $c->month - 1; // 0-based, biar sinkron dgn select option value
        $this->jumpYear = $c->year;
    }

    // "Hari ini" tetap di-pin seperti versi JS aslinya
    protected function today(): Carbon
    {
        return Carbon::now();
    }

    protected function cursorDate(): Carbon
    {
        return Carbon::parse($this->cursor);
    }

    /**
     * Ambil semua event milik user yang login dari DB, lalu expand jadi
     * array per-tanggal: ['2026-06-03' => [['id'=>.., 'label'=>.., 'color'=>..], ...]]
     */
    #[Computed]
    public function events(): array
    {
        $rows = Calendar::query()
            ->where('user_id', auth()->id())
            ->get();

        $map = [];

        foreach ($rows as $row) {
            $start = Carbon::parse($row->start_date);
            $end = Carbon::parse($row->end_date);

            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->toDateString();
                $map[$key] ??= [];

                $label = $row->event_title;
                $sortKey = '';

                if (! $row->is_all_day && $row->start_time) {
                    $label = Carbon::parse($row->start_time)->format('H:i') . '-' . Carbon::parse($row->end_time)->format('H:i') . ' ' . $label;
                    $sortKey = $row->start_time;
                }

                $map[$key][] = [
                    'id' => $row->calendar_id,
                    'label' => $label,
                    'color' => $row->color,
                    'sort_key' => $sortKey,
                    'is_done' => (bool) $row->is_done,
                ];
            }
        }

        foreach ($map as $key => $events) {
            usort($events, fn($a, $b) => $a['sort_key'] <=> $b['sort_key']);
            $map[$key] = $events;
        }

        return $map;
    }

    // ===== Navigasi =====
    public function prev(): void
    {
        $d = $this->cursorDate();
        match ($this->view) {
            'month' => $d->subMonth(),
            'week' => $d->subWeek(),
            default => $d->subDay(),
        };
        $this->cursor = $d->toDateString();
    }

    public function next(): void
    {
        $d = $this->cursorDate();
        match ($this->view) {
            'month' => $d->addMonth(),
            'week' => $d->addWeek(),
            default => $d->addDay(),
        };
        $this->cursor = $d->toDateString();
    }

    public function goToday(): void
    {
        $this->cursor = $this->today()->toDateString();
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function applyJump(): void
    {
        $this->cursor = Carbon::create($this->jumpYear, $this->jumpMonth + 1, 1)->toDateString();
    }

    // ===== Form helpers =====

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->eventTitle = '';
        $this->eventDescription = '';
        $this->eventStartTime = '';
        $this->eventEndTime = '';
        $this->isAllDay = true;
        $this->selectedColor = 'violet';
        $this->isViewMode = false;
        $this->isDone = false;
        $this->resetErrorBag();
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->pendingDate = null;
        $this->eventStart = '';
        $this->eventEnd = '';
    }

    public function startView(int $id): void
    {
        $row = Calendar::where('user_id', auth()->id())->findOrFail($id);

        $this->resetForm();
        $this->editingId = $row->calendar_id;
        $this->eventTitle = $row->event_title;
        $this->eventDescription = $row->event_description ?? '';
        $this->eventStart = $row->start_date->toDateString();
        $this->eventEnd = $row->end_date->toDateString();
        $this->eventStartTime = $row->start_time ? substr($row->start_time, 0, 5) : '';
        $this->eventEndTime = $row->end_time ? substr($row->end_time, 0, 5) : '';
        $this->isAllDay = (bool) $row->is_all_day;
        $this->selectedColor = $row->color;
        $this->isDone = (bool) $row->is_done;
        $this->isViewMode = true;
    }

    /** Dipanggil saat user klik tombol "Edit" di modal detail -> pindah ke mode form edit */
    public function switchToEdit(): void
    {
        $this->isViewMode = false;
    }

    /** Dipanggil dari Alpine saat klik cell kosong -> mode "create" */
    public function startCreate(string $date): void
    {
        $this->resetForm();
        $this->pendingDate = $date;
        $this->eventStart = $date;
        $this->eventEnd = $date;
    }
    public function startEdit(int $id): void
    {
        $row = Calendar::where('user_id', auth()->id())->findOrFail($id);

        $this->resetForm();
        $this->editingId = $row->calendar_id;
        $this->eventTitle = $row->event_title;
        $this->eventDescription = $row->event_description ?? '';
        $this->eventStart = $row->start_date->toDateString();
        $this->eventEnd = $row->end_date->toDateString();
        $this->eventStartTime = $row->start_time ? substr($row->start_time, 0, 5) : '';
        $this->eventEndTime = $row->end_time ? substr($row->end_time, 0, 5) : '';
        $this->isAllDay = (bool) $row->is_all_day;
        $this->selectedColor = $row->color;
        $this->isDone = (bool) $row->is_done;
    }

    // ===== CRUD =====

    public function saveEvent(): void
    {
        $title = trim($this->eventTitle);

        $this->validate([
            'eventTitle' => 'required|string|max:255',
            'eventStart' => 'required|date',
            'eventEnd' => 'required|date',
            'selectedColor' => 'required|in:violet,teal,pink,blue,amber',
            'eventStartTime' => 'nullable',
            'eventEndTime' => 'nullable',
        ], [
            'eventTitle.required' => 'Title is required.',
        ]);

        $start = Carbon::parse($this->eventStart ?: $this->pendingDate);
        $end = Carbon::parse($this->eventEnd ?: $this->eventStart ?: $this->pendingDate);

        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $payload = [
            'user_id' => auth()->id(),
            'event_title' => $title,
            'event_description' => $this->eventDescription ?: null,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'start_time' => $this->isAllDay ? null : ($this->eventStartTime ?: null),
            'end_time' => $this->isAllDay ? null : ($this->eventEndTime ?: null),
            'is_all_day' => $this->isAllDay,
            'color' => $this->selectedColor,
            'is_done' => $this->isDone,
        ];

        if ($this->editingId) {
            Calendar::where('user_id', auth()->id())
                ->where('calendar_id', $this->editingId)
                ->update($payload);
        } else {
            Calendar::create($payload);
        }

        unset($this->events); // bust computed cache
        $this->resetForm();
    }

    public function deleteEvent(): void
    {
        if ($this->editingId) {
            Calendar::where('user_id', auth()->id())
                ->where('calendar_id', $this->editingId)
                ->delete();

            unset($this->events);
            $this->resetForm();
        }
    }

    // Judul toolbar
    #[Computed]
    public function title(): string
    {
        $d = $this->cursorDate();

        if ($this->view === 'day') {
            return $d->translatedFormat('F j, Y');
        }

        if ($this->view === 'week') {
            $days = $this->weekDays;
            $start = $days[0];
            $end = $days[count($days) - 1];

            $startLabel = $start->translatedFormat('M j');
            $endLabel = ($start->month === $end->month ? '' : $end->translatedFormat('M') . ' ') . $end->format('j, Y');

            return "{$startLabel} \u{2013} {$endLabel}";
        }

        return $d->translatedFormat('F Y');
    }

    // 42 sel untuk grid bulan
    #[Computed]
    public function monthCells(): array
    {
        $d = $this->cursorDate();
        $firstOfMonth = $d->copy()->startOfMonth();
        $start = $firstOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $events = $this->events;

        $cells = [];
        for ($i = 0; $i < 42; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();

            $cells[] = [
                'date' => $date,
                'key' => $key,
                'day' => $date->day,
                'otherMonth' => $date->month !== $d->month,
                'isToday' => $date->isSameDay($this->today()),
                'events' => $events[$key] ?? [],
            ];
        }

        return $cells;
    }

    // Hari-hari yang ditampilkan utk view week/day
    #[Computed]
    public function weekDays(): array
    {
        $d = $this->cursorDate();

        if ($this->view === 'day') {
            return [$d->copy()];
        }

        $start = $d->copy()->startOfWeek(Carbon::SUNDAY);

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        return $days;
    }

    public function toggleDone(): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->isDone = ! $this->isDone;

        Calendar::where('user_id', auth()->id())
            ->where('calendar_id', $this->editingId)
            ->update(['is_done' => $this->isDone]);

        unset($this->events); // bust computed cache, biar tampilan grid ikut update
    }
};
?>

<div class="col-lg-12 d-flex align-items-stretch mt-4" x-data="{ modalOpen: false }">
    <div class="card w-100">
        <div class="card-body p-4">

            <div class="fc-modernize" id="fcModernizeRoot" wire:ignore.self>
                <h2 class="sr-only">Kalender</h2>
                <div class="cal-wrap">
                    <div class="cal-toolbar">
                        <div class="cal-nav">
                            <button class="btn-nav" type="button" wire:click="prev" wire:loading.attr="disabled" wire:target="prev,next,goToday,applyJump" aria-label="Sebelumnya">
                                <i class="ti ti-chevron-left" aria-hidden="true"></i>
                            </button>
                            <button class="btn-nav" type="button" wire:click="next" wire:loading.attr="disabled" wire:target="prev,next,goToday,applyJump" aria-label="Selanjutnya">
                                <i class="ti ti-chevron-right" aria-hidden="true"></i>
                            </button>
                            <button class="btn-today" type="button" wire:click="goToday" wire:loading.attr="disabled" wire:target="goToday">
                                <span wire:loading wire:target="goToday" class="spinner-border spinner-border-sm me-1"></span>
                                Today
                            </button>
                        </div>

                        <div class="cal-title-wrap" id="fcTitleWrap" x-data="{ open: false }" @click.outside="open = false">
                            <button class="cal-title cal-title-btn" type="button" @click="open = !open">
                                {{ $this->title }}
                            </button>

                            <div class="fc-jump-popover" :class="{ open: open }" x-show="open" x-cloak @click.stop>
                                <div class="fc-jump-row">
                                    <select wire:model="jumpMonth" class="fc-jump-select">
                                        @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $name)
                                        <option value="{{ $i }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model="jumpYear" class="fc-jump-select">
                                        @php($base = $this->today()->year)
                                        @for ($y = $base - 10; $y <= $base + 10; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                            @endfor
                                    </select>
                                </div>
                                <button type="button" class="fc-jump-go" @click="open = false" wire:click="applyJump">Go</button>
                            </div>
                        </div>
                        <button class="btn-add-event" type="button"
                            @click="modalOpen = true; $wire.startCreate('{{ $this->cursorDate()->toDateString() }}')">
                            <i class="ti ti-plus" aria-hidden="true"></i>
                            Add Event
                        </button>
                    </div>

                    {{-- ===== MONTH VIEW ===== --}}
                    @if ($view === 'month')
                    <div wire:loading.class="fc-is-loading" wire:target="prev,next,goToday,applyJump,setView" class="fc-body-wrap">
                        <div id="fcMonthView">
                            <div class="cal-head" id="fcHead">
                                @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)
                                <div class="cal-head-cell">{{ $dayName }}</div>
                                @endforeach
                            </div>
                            <div class="cal-body" id="calBody2">
                                @foreach ($this->monthCells as $cell)
                                <div
                                    class="cal-cell {{ $cell['otherMonth'] ? 'other-month' : '' }} {{ $cell['isToday'] ? 'today' : '' }}"
                                    @click="$wire.startCreate('{{ $cell['key'] }}').then(() => modalOpen = true)"
                                    wire:key="month-cell-{{ $cell['key'] }}">
                                    <div class="day-num">{{ $cell['day'] }}</div>

                                    @foreach ($cell['events'] as $ev)
                                    <div class="event {{ $ev['color'] }} {{ $ev['is_done'] ? 'is-done' : '' }}"
                                        @click.stop="$wire.startView({{ $ev['id'] }}).then(() => modalOpen = true)">
                                        @if ($ev['is_done'])
                                        <i class="ti ti-check fc-event-check" aria-hidden="true"></i>
                                        @endif
                                        {{ $ev['label'] }}
                                    </div>
                                    @endforeach
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Add / Edit / View Event --}}
        <div class="fc-modal-overlay" :class="{ open: modalOpen }" @click="modalOpen = false; $wire.closeModal()">
            <div class="fc-modal" role="dialog" aria-modal="true" aria-labelledby="fcModalTitle" @click.stop>
                <div class="fc-modal-header">
                    <h3 id="fcModalTitle">
                        {{ $isViewMode ? 'Event Detail' : ($editingId ? 'Edit Event' : 'Add Event') }}
                    </h3>
                    <button type="button" class="fc-modal-close" @click="modalOpen = false; $wire.closeModal()" aria-label="Tutup">
                        <i class="ti ti-x" aria-hidden="true"></i>
                    </button>
                </div>

                @if ($isViewMode)
                {{-- ===== MODE VIEW (read-only) ===== --}}
                <div class="fc-view-detail">
                    <div class="fc-view-title-row">
                        <span class="fc-view-dot {{ $selectedColor }}" aria-hidden="true"></span>

                        <h4 class="fc-view-title">{{ $eventTitle }}</h4>
                    </div>

                    <div class="fc-view-meta">
                        <div class="fc-view-row">
                            <i class="ti ti-calendar" aria-hidden="true"></i>
                            <span>
                                {{ \Carbon\Carbon::parse($eventStart)->translatedFormat('D, j M Y') }}
                                @if ($eventStart !== $eventEnd)
                                &ndash; {{ \Carbon\Carbon::parse($eventEnd)->translatedFormat('D, j M Y') }}
                                @endif
                            </span>
                        </div>

                        <div class="fc-view-row">
                            <i class="ti ti-clock" aria-hidden="true"></i>
                            <span>
                                @if (! $isAllDay && $eventStartTime)
                                {{ $eventStartTime }} &ndash; {{ $eventEndTime }}
                                @else
                                All day
                                @endif
                            </span>
                        </div>

                        @if ($eventDescription)
                        <div class="fc-view-row fc-view-desc">
                            <i class="ti ti-align-left" aria-hidden="true"></i>
                            <span>{{ $eventDescription }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="fc-view-actions">
                    <button
                        type="button"
                        class="btn btn-success"
                        wire:click="toggleDone"
                        @disabled($isDone)>
                        <i class="ti {{ $isDone ? 'ti-check' : 'ti-circle-check' }}" aria-hidden="true"></i>
                        {{ $isDone ? 'Selesai' : 'Tandai Selesai' }}
                    </button>

                    @if (!$isDone)
                    <button type="button" class="btn btn-primary" wire:click="switchToEdit">
                        <i class="ti ti-pencil" aria-hidden="true"></i>
                        Edit
                    </button>
                    @endif
                    <button
                        type="button"
                        class="btn btn-danger"
                        @click="modalOpen = false"
                        wire:click="deleteEvent">
                        <i class="ti ti-trash" aria-hidden="true"></i>
                        Delete
                    </button>
                </div>

                @else
                {{-- ===== MODE FORM (create / edit) ===== --}}
                <p class="fc-modal-desc">Fill in the title, choose the event color, and select the start and end dates to add an event.</p>

                <div class="fc-field">
                    <label for="fcEventTitle">Event Title</label>
                    <input type="text" id="fcEventTitle" wire:model="eventTitle" placeholder="">
                    @error('eventTitle')
                    <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="fc-field">
                    <label for="fcEventDescription">Description</label>
                    <textarea id="fcEventDescription" wire:model="eventDescription" rows="2"></textarea>
                </div>

                <div class="fc-field">
                    <label for="fcEventStart">Start Date</label>
                    <input type="date" id="fcEventStart" wire:model="eventStart">
                </div>

                <div class="fc-field">
                    <label for="fcEventEnd">End Date</label>
                    <input type="date" id="fcEventEnd" wire:model="eventEnd">
                </div>

                <div class="fc-field">
                    <label>
                        <input type="checkbox" wire:model.live="isAllDay">
                        All day
                    </label>
                </div>

                @if (! $isAllDay)
                <div class="fc-field">
                    <label for="fcEventStartTime">Start Time</label>
                    <input type="time" id="fcEventStartTime" wire:model="eventStartTime">
                </div>

                <div class="fc-field">
                    <label for="fcEventEndTime">End Time</label>
                    <input type="time" id="fcEventEndTime" wire:model="eventEndTime">
                </div>
                @endif

                <div class="fc-field">
                    <label>Event Color</label>
                    <div class="fc-color-options" id="fcColorOptions">
                        @foreach ($this->colors() as $color)
                        <button
                            type="button"
                            class="fc-color-dot {{ $color }}"
                            :class="{ active: $wire.selectedColor === '{{ $color }}' }"
                            @click="$wire.set('selectedColor', '{{ $color }}', false)"
                            aria-label="{{ ucfirst($color) }}"></button>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary"
                        @click="modalOpen = false"
                        wire:click="saveEvent">{{ $editingId ? 'Update Event' : 'Add Event' }}</button>

                    @if ($editingId)
                    <button type="button" class="btn btn-danger"
                        @click="modalOpen = false"
                        wire:click="deleteEvent">Delete</button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>