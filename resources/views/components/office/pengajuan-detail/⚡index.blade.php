<?php

use App\Models\Document;
use Livewire\Component;

new class extends Component
{
    public $selectedDocumentId = null;

    protected $listeners = ['akun-created' => '$refresh'];

    public function lihatProgress($documentId)
    {
        $this->selectedDocumentId = $documentId;
    }

    public function tutupProgress()
    {
        $this->selectedDocumentId = null;
    }

    public function render()
    {
        return $this->view()
            ->with([
                'documents' => Document::with(['documentRoute.departement'])
                    ->where('created_by', auth()->id())
                    ->latest()
                    ->get(),

                'selectedDocument' => $this->selectedDocumentId
                    ? Document::with([
                        'documentRoute' => fn($q) => $q->orderBy('urutan'),
                        'documentRoute.departement',
                    ])->find($this->selectedDocumentId)
                    : null,
            ])
            ->layout('layouts.main')
            ->title('DocTracker | Dashboard');
    }
};
?>

<div>
    {{-- List Dokumen --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Judul Dokumen</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Dibuat</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                    @php
                    $total = $doc->documentRoute->count();
                    $done = $doc->documentRoute->where('status', 'approved')->count();
                    $pct = $total > 0 ? round(($done / $total) * 100) : 0;
                    $priorityColor = [
                    'mendesak' => 'danger',
                    'penting' => 'warning',
                    'normal' => 'secondary',
                    ][$doc->priority] ?? 'secondary';
                    $statusColor = [
                    'pending' => 'warning',
                    'revisi' => 'info',
                    'approved' => 'success',
                    'selesai' => 'success',
                    'hilang' => 'dark',
                    ][$doc->current_status] ?? 'secondary';
                    @endphp
                    <tr class="{{ $selectedDocumentId == $doc->document_id ? 'table-primary' : '' }}">
                        <td>{{ ucfirst($doc->judul_dokumen) }}</td>
                        <td>
                            <span class="badge bg-{{ $priorityColor }}">
                                {{ ucfirst($doc->priority) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColor }}">
                                {{ ucfirst($doc->current_status) }}
                            </span>
                        </td>
                        <td style="min-width: 150px">
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 6px">
                                    <div class="progress-bar" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $done }}/{{ $total }}</small>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $doc->created_at->format('d M Y') }}
                            </small>
                        </td>
                        <td>
                            <button
                                wire:click="lihatProgress({{ $doc->document_id }})"
                                class="btn btn-sm btn-outline-primary">
                                Lihat Progress
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Belum ada dokumen.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Detail Progress / Stepper --}}
    @if ($selectedDocument)
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">{{ ucfirst($selectedDocument->judul_dokumen) }}</h5>
                <small class="text-muted">{{ $selectedDocument->tracking_code }}</small>
            </div>
            <button wire:click="tutupProgress" class="btn-close"></button>
        </div>

        <div class="card-body">
            @php
            $steps = $selectedDocument->documentRoute->sortBy('urutan');
            $total = $steps->count();
            @endphp

            <div class="d-flex align-items-start">
                @foreach ($steps as $index => $step)
                @php
                $isLast = $loop->last;
                $stepColor = match($step->status) {
                'approved' => 'success',
                'rejected' => 'danger',
                'revisi' => 'warning',
                'hilang' => 'dark',
                'pending' => 'primary',
                default => 'secondary',
                };
                $stepIcon = match($step->status) {
                'approved' => '✓',
                'rejected' => '✕',
                default => $step->urutan,
                };
                @endphp

                <div class="d-flex flex-column align-items-center" style="min-width: 80px">
                    {{-- Circle --}}
                    <div
                        class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                        style="width:36px;height:36px;background:var(--bs-{{ $stepColor }});font-size:14px;flex-shrink:0">
                        {{ $stepIcon }}
                    </div>
                    {{-- Nama Departemen --}}
                    <small class="text-center mt-1" style="font-size:11px;max-width:72px">
                        {{ $step->departement->nama_departement }}
                    </small>
                    {{-- Note jika ada --}}
                    @if ($step->note)
                    <small class="text-muted text-center fst-italic" style="font-size:10px;max-width:72px">
                        "{{ $step->note }}"
                    </small>
                    @endif
                    {{-- Badge revisi jika step ini pernah jadi target revisi --}}
                    @if ($step->revisi)
                    <span class="badge bg-warning text-dark mt-1" style="font-size:9px">
                        revisi dari step {{ $step->revisi }}
                    </span>
                    @endif
                </div>

                {{-- Connector line --}}
                @if (!$isLast)
                <div
                    class="flex-grow-1 mt-2"
                    style="height:2px;background:var(--bs-{{ $stepColor }});opacity:0.4;min-width:24px">
                </div>
                @endif

                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>