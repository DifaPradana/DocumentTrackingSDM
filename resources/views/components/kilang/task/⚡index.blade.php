<?php

use App\Models\Document;
use App\Models\DocumentRoute;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{

    use WithPagination;
    public $perPage = 20;
    public $search = '';

    public $selectedDocumentId = null;

    public function lihatProgress($documentId)
    {
        $this->batalEditStatus();
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
                    ->when(
                        $this->search,
                        fn($q) =>
                        $q->where('judul_dokumen', 'like', "%{$this->search}%")
                    )
                    ->latest()
                    ->paginate($this->perPage),

                'selectedDocument' => $this->selectedDocumentId
                    ? Document::with([
                        'documentRoute' => fn($q) => $q->orderBy('urutan')->with('departement'),
                    ])->find($this->selectedDocumentId)
                    : null,
            ])
            ->layout('layouts.main')
            ->title('DocTracker | Task');
    }

    // Properti untuk edit
    public $editingStepId = null;
    public $editStatus = '';
    public $editNote = '';

    public function bukaEditStatus($stepId)
    {
        if ($this->editingStepId === $stepId) {
            $this->batalEditStatus();
            return;
        }

        $step = DocumentRoute::findOrFail($stepId);
        $this->editingStepId = $stepId;
        $this->editStatus = $step->status;
        $this->editNote = $step->note ?? '';
    }

    public function batalEditStatus()
    {
        $this->editingStepId = null;
        $this->editStatus = '';
        $this->editNote = '';
    }

    public function simpanEditStatus()
    {
        $step = DocumentRoute::findOrFail($this->editingStepId);

        $step->update([
            'status' => $this->editStatus,
            'note'   => $this->editNote ?: null,
        ]);

        $document = Document::findOrFail($step->document_id);

        if ($this->editStatus === 'approved') {
            // Cari step selanjutnya
            $nextStep = DocumentRoute::where('document_id', $step->document_id)
                ->where('urutan', '>', $step->urutan)
                ->orderBy('urutan')
                ->first();

            if ($nextStep) {
                // Masih ada step selanjutnya → pending
                $nextStep->update(['status' => 'pending']);
                $document->update(['current_status' => 'pending']);
            } else {
                // Tidak ada step lagi → dokumen selesai
                $document->update(['current_status' => 'selesai']);
            }
        } elseif ($this->editStatus === 'revisi') {
            $document->update(['current_status' => 'revisi']);
        }

        $this->batalEditStatus();

        $docId = $this->selectedDocumentId;
        $this->selectedDocumentId = null;
        $this->selectedDocumentId = $docId;
    }
};
?>

<div class="col-lg-12 d-flex align-items-stretch mt-4">
    <div class="card w-100">
        <div class="card-body p-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <i class="ti ti-file-text me-2"></i>Dokumen
                        </h5>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="flex">
                        <div class="relative w-full">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 "
                                placeholder="Search" required="">
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div class="row g-3">
                {{-- Tabel --}}
                <div class="{{ $selectedDocument ? 'col-lg-7' : 'col-12' }}">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-center">
                                <tr>
                                    <th class="ps-3 small text-muted fw-semibold">Judul Dokumen</th>
                                    <th class="small text-muted fw-semibold">Prioritas</th>
                                    <th class="small text-muted fw-semibold">Status</th>
                                    <th class="small text-muted fw-semibold">Posisi Dokumen</th>
                                    <th class="small text-muted fw-semibold">Progress</th>
                                    <th class="small text-muted fw-semibold">Dibuat</th>
                                    <th class="small text-muted fw-semibold">Deadline</th>
                                    <th class="small text-muted fw-semibold"></th>
                                </tr>
                            </thead>
                            <tbody class="text-center">
                                @forelse ($documents as $doc)
                                @php
                                $total = $doc->documentRoute->count();
                                $done = $doc->documentRoute->where('status', 'approved')->count();
                                $pct = $total > 0 ? round(($done / $total) * 100) : 0;

                                $priorityMap = [
                                'tinggi' => ['bg-danger-subtle text-danger-emphasis', 'Tinggi'],
                                'sedang' => ['bg-warning-subtle text-warning-emphasis', 'Sedang'],
                                'rendah' => ['bg-secondary-subtle text-secondary-emphasis', 'Rendah'],
                                ];
                                [$priorityClass, $priorityLabel] = $priorityMap[$doc->priority]
                                ?? ['bg-secondary-subtle text-secondary-emphasis', ucfirst($doc->priority)];

                                $statusMap = [
                                'pending' => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
                                'waiting' => ['bg-primary-subtle text-primary-emphasis', 'Waiting'],
                                'revisi' => ['bg-danger-subtle text-danger-emphasis', 'Revisi'],
                                'approved' => ['bg-success-subtle text-success-emphasis', 'Approved'],
                                'selesai' => ['bg-success-subtle text-success-emphasis', 'Selesai'],
                                'hilang' => ['bg-dark-subtle text-dark-emphasis', 'Hilang'],
                                ];
                                [$statusClass, $statusLabel] = $statusMap[$doc->current_status]
                                ?? ['bg-secondary-subtle text-secondary-emphasis', ucfirst($doc->current_status)];

                                $isDeadlineSoon = $doc->deadline &&
                                $doc->deadline->isFuture() &&
                                now()->diffInDays($doc->deadline) <= 3 &&
                                    $doc->current_status !== 'selesai';
                                    @endphp

                                    <tr class="{{ $selectedDocumentId == $doc->document_id ? 'table-primary' : '' }}"
                                        style="cursor:pointer"
                                        wire:click="lihatProgress({{ $doc->document_id }})">

                                        <td class="ps-3">
                                            <span class="fw-medium">{{ ucfirst($doc->judul_dokumen) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $priorityClass }}">{{ $priorityLabel }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="ps-3">
                                            @php
                                            $currentStep = $doc->documentRoute
                                            ->firstWhere('status', 'pending');
                                            @endphp

                                            <span class="fw-medium">
                                                {{ $currentStep?->departement?->nama_departement ?? '-' }}
                                            </span>
                                        </td>
                                        <td style="min-width:130px">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height:5px">
                                                    <div class="progress-bar {{ $pct == 100 ? 'bg-success' : '' }}"
                                                        style="width:{{ $pct }}%"></div>
                                                </div>
                                                <small class="text-muted" style="white-space:nowrap">{{ $done }}/{{ $total }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $doc->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td>
                                            @if ($doc->deadline)
                                            <small class="{{ $isDeadlineSoon ? 'text-danger fw-semibold' : 'text-muted' }}">
                                                {{ $isDeadlineSoon ? '⚠ ' . $doc->deadline->format('d M Y') : $doc->deadline->format('d M Y') }}
                                            </small>
                                            @else
                                            <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <i class="ti ti-chevron-right text-muted"></i>
                                        </td>
                                    </tr>

                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                                            Belum ada dokumen.
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="py-4 px-3">
                        <div class="flex ">
                            <div class="flex space-x-4 items-center mb-3">
                                <label class="w-32 text-sm font-medium text-gray-900">Per Page</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        {{ $documents->links() }}
                    </div>
                </div>

                {{-- Detail Progress --}}
                @if ($selectedDocument)
                @php $steps = $selectedDocument->documentRoute; @endphp
                <div class="col-lg-5">
                    <div class="card h-100 shadow-sm">
                        <!-- Header Detail Progress -->
                        <div class="card-header d-flex justify-content-between align-items-start border-bottom py-3">
                            <div>
                                <h6 class="mb-0 fw-semibold">{{ ucfirst($selectedDocument->judul_dokumen) }}</h6>
                                <div class="mt-1 d-flex gap-1 flex-wrap">
                                    @php
                                    [$pc, $pl] = $priorityMap[$selectedDocument->priority]
                                    ?? ['bg-secondary-subtle text-secondary-emphasis', ucfirst($selectedDocument->priority)];
                                    [$sc, $sl] = $statusMap[$selectedDocument->current_status]
                                    ?? ['bg-secondary-subtle text-secondary-emphasis', ucfirst($selectedDocument->current_status)];
                                    @endphp
                                    <span class="badge {{ $pc }}" style="font-size:10px">Prioritas : {{ $pl }}</span>
                                    <span class="badge {{ $sc }}" style="font-size:10px">{{ $sl }}</span>
                                    @if ($selectedDocument->deadline)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size:10px">
                                        <i class="ti ti-calendar me-1"></i>{{ $selectedDocument->deadline->format('d M Y') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <button wire:click="tutupProgress" class="btn-close ms-2 flex-shrink-0"></button>
                        </div>

                        <div class="card-body overflow-auto" style="max-height: 480px">
                            <ul class="timeline-widget mb-0 position-relative mb-n5">

                                @foreach ($steps as $index => $step)
                                @php
                                $bulletColor = match($step->status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                'revisi' => 'warning',
                                'hilang' => 'dark',
                                'waiting' => 'primary',
                                'pending' => 'warning',
                                default => 'secondary',
                                };
                                $badgeClass = match($step->status) {
                                'approved' => 'bg-success-subtle text-success-emphasis',
                                'rejected' => 'bg-danger-subtle text-danger-emphasis',
                                'revisi' => 'bg-danger-subtle text-danger-emphasis',
                                'hilang' => 'bg-dark-subtle text-dark-emphasis',
                                'waiting' => 'bg-primary-subtle text-primary-emphasis',
                                'pending' => 'bg-warning-subtle text-warning-emphasis',
                                default => 'bg-secondary-subtle text-secondary-emphasis',
                                };
                                $statusLabel = match($step->status) {
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'revisi' => 'Revisi',
                                'hilang' => 'Hilang',
                                'waiting' => 'Waiting',
                                'pending' => 'Pending',
                                default => ucfirst($step->status),
                                };

                                @endphp
                                <li wire:key="step-{{ $step->document_route_id }}" class="timeline-item d-flex position-relative overflow-hidden">
                                    <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                                        <span class="timeline-badge border-2 bg-{{ $bulletColor }} flex-shrink-0 my-8"></span>
                                        @if (!$loop->last)
                                        <span class="timeline-badge-border d-block flex-shrink-0"></span>
                                        @endif
                                    </div>
                                    <div class="timeline-desc fs-3 text-dark mt-n1 w-100">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <span class="fw-semibold">{{ $step->departement->nama_departement }}</span>
                                                <span class="badge {{ $badgeClass }} ms-1" style="font-size:10px">
                                                    {{ $statusLabel }}
                                                </span>
                                                @if ($step->note)
                                                <div class="text-muted fst-italic mt-1" style="font-size:11px">
                                                    "{{ $step->note }}"
                                                </div>
                                                @endif
                                                @if ($step->revisi)
                                                <div class="mt-1">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size:10px">
                                                        Revisi dari step {{ $step->revisi }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Inline Edit Form --}}
                                        @php
                                        $hasPreviousRevisi = collect($steps)
                                        ->take($index)
                                        ->contains(fn ($item) => $item->status === 'revisi');

                                        $canEdit =
                                        in_array($step->status, ['pending', 'waiting']) &&
                                        !$hasPreviousRevisi;
                                        @endphp
                                        @if ($editingStepId == $step->document_route_id)

                                        <div class="mt-2 p-2 rounded border bg-light">
                                            <div class="mb-2">
                                                <label class="form-label mb-1" style="font-size:11px; font-weight:600;">Status</label>
                                                <select wire:model="editStatus" class="form-select form-select-sm">
                                                    <option value="pending">Pending</option>
                                                    <option value="waiting">Waiting</option>
                                                    <option value="approved">Approved</option>
                                                    <option value="revisi">Revisi</option>
                                                    <option value="hilang">Hilang</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label mb-1" style="font-size:11px; font-weight:600;">Catatan</label>
                                                <textarea
                                                    wire:model="editNote"
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Tambahkan catatan... (opsional)"
                                                    style="font-size:11px"></textarea>
                                            </div>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button
                                                    wire:click="batalEditStatus"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    style="font-size:11px; padding: 2px 8px;">Batal</button>
                                                <button
                                                    wire:click="simpanEditStatus"
                                                    class="btn btn-sm btn-primary"
                                                    style="font-size:11px; padding: 2px 8px;">
                                                    <i class="ti ti-check me-1"></i>Simpan
                                                </button>
                                            </div>
                                        </div>
                                        @else

                                        @if ($canEdit)
                                        <button
                                            wire:click="bukaEditStatus({{ $step->document_route_id }})"
                                            class="btn btn-sm btn-outline-primary mt-2">
                                            Edit
                                        </button>
                                        @endif
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>