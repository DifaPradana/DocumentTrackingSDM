<?php

use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

new class extends Component
{

    use WithPagination, WithFileUploads;
    public $perPage = 20;
    public $search = '';
    public $photo_revisi;
    public $photo_done;
    public $selectedDocumentId = null;
    public $editingStepId = null;
    public $editStatus = '';
    public $editNote = '';
    public $pengantar_user_id;
    public $showDoneModal = false;
    public $pendingDocumentId = null;
    public $pendingStepId = null;


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
                    ->where('current_status', '!=', 'done')
                    ->latest()
                    ->paginate($this->perPage),

                'selectedDocument' => $this->selectedDocumentId
                    ? Document::with([
                        'documentRoute' => fn($q) => $q->orderBy('urutan')->with('departement'),
                    ])->find($this->selectedDocumentId)
                    : null,

                'pengantarUsers' => User::whereHas('role', function ($q) {
                    $q->where('nama_role', 'Pengantar');
                })->get(),
            ])
            ->layout('layouts.main')
            ->title('DocTracker | Task');
    }

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

        $dataUpdate = [
            'status' => $this->editStatus,
            'note'   => $this->editNote ?: null,
        ];

        if ($this->editStatus === 'onprocess' && is_null($step->kapan_onprocess)) {
            $dataUpdate['kapan_onprocess'] = now();
        }

        if ($this->editStatus === 'approved') {
            $dataUpdate['kapan_approved'] = now();
        }

        $step->update($dataUpdate);

        $document = Document::findOrFail($step->document_id);

        $document->update([
            'current_status' => $this->editStatus
        ]);

        if ($this->editStatus === 'approved') {
            // Cari step selanjutnya
            $nextStep = DocumentRoute::where('document_id', $step->document_id)
                ->where('urutan', '>', $step->urutan)
                ->orderBy('urutan')
                ->first();

            if ($nextStep) {
                $nextStep->update([
                    'status' => 'unprocessed'
                ]);

                $document->update([
                    'current_status' => 'unprocessed'
                ]);
            } else {
                $this->pendingDocumentId = $document->document_id;
                $this->pendingStepId     = $step->document_route_id;

                // Reset edit form dulu
                $this->editingStepId = null;
                $this->editStatus    = '';
                $this->editNote      = '';

                // Buka modal SETELAH reset, jangan pakai batalEditStatus() karena itu tutup modal
                $this->showDoneModal = true;
                return;
            }
        } elseif ($this->editStatus === 'revisi') {
            $document->update([
                'current_status' => 'revisi'
            ]);
        }

        $this->batalEditStatus();

        $docId = $this->selectedDocumentId;
        $this->selectedDocumentId = null;
        $this->selectedDocumentId = $docId;
    }


    public function simpanDoneModal()
    {
        $this->validate([
            'photo_done'        => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'pengantar_user_id' => 'required|exists:users,user_id',
        ], [
            'photo_done.required' => 'Foto/dokumen bukti wajib diupload.',
            'photo_done.mimes'    => 'File harus berupa gambar (JPG, PNG) atau PDF.',
            'photo_done.max'      => 'Ukuran file maksimal 10MB.',
            'pengantar_user_id.required' => 'Pengantar wajib dipilih.',
        ]);

        $path = null;

        if ($this->photo_done) {
            $uploadedFile = $this->photo_done;
            $filename     = Str::uuid() . '.' . $uploadedFile->extension();
            $relativePath = 'bukti-dokumen/' . $filename;
            $fullPath     = storage_path('app/public/' . $relativePath);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $mimeType = $uploadedFile->getMimeType();

            if (str_starts_with($mimeType, 'image/')) {
                $manager = ImageManager::usingDriver(Driver::class);
                $image   = $manager->decodeSplFileInfo($uploadedFile);

                if ($image->width() > 1200) {
                    $image->scale(width: 1200);
                }

                $image->save($fullPath, quality: 70);
            } else {
                // PDF — copy langsung tanpa kompresi
                \Storage::disk('public')->put(
                    $relativePath,
                    file_get_contents($uploadedFile->getRealPath())
                );
            }

            $path = $relativePath;
        }

        Document::findOrFail($this->pendingDocumentId)->update([
            'current_status' => 'done',
            'photo_done'     => $path,
            'pengantar_id'   => $this->pengantar_user_id,
        ]);

        $this->showDoneModal     = false;
        $this->photo_done        = null;
        $this->pengantar_user_id = null;
        $this->pendingDocumentId = null;
        $this->pendingStepId     = null;

        $docId = $this->selectedDocumentId;
        $this->selectedDocumentId = null;
        $this->selectedDocumentId = $docId;
    }

    public function tutupDoneModal()
    {
        $this->showDoneModal    = false;
        $this->photo_done       = null;
        $this->pengantar_user_id = null;
        $this->pendingDocumentId = null;
        $this->pendingStepId    = null;
    }
};
?>

<div wire:poll.15s class="col-lg-12 d-flex align-items-stretch mt-4">
    <div class="card w-100">
        <div class="card-body p-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <i class="ti ti-file-text me-2"></i>Dokumen Yang Harus Dikerjakan
                        </h5>
                    </div>
                </div>
                <br>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="flex">
                        <div class="relative w-full">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                                placeholder="Search" required="">
                        </div>
                    </div>
                </div>
            </div>
            <br>

            @if ($showDoneModal)
            <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.45); z-index:1055;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

                        <div class="modal-header border-0 px-4 pt-4 pb-0">
                            <div>
                                <span class="badge bg-success-subtle text-success-emphasis rounded-pill px-3 py-2" style="font-size:12px">
                                    <i class="ti ti-circle-check me-1"></i>Dokumen Selesai
                                </span>
                                <p class="text-muted mb-0 mt-1" style="font-size:12px">
                                    Upload foto bukti serah terima dan pilih pengantar untuk menyelesaikan dokumen ini.
                                </p>
                            </div>
                            <button type="button" wire:click="tutupDoneModal" class="btn-close ms-auto flex-shrink-0"></button>
                        </div>

                        <div class="modal-body px-4 py-3">

                            {{-- Upload Foto/PDF Done --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold" style="font-size:12px">
                                    <i class="ti ti-paperclip me-1"></i>
                                    Foto/PDF Bukti Selesai <span class="text-danger">*</span>
                                </label>
                                <p class="mb-1 text-muted" style="font-size:10px">
                                    PNG, JPG, JPEG, PDF · Maks 10 MB
                                </p>

                                <label for="photoDoneInput"
                                    class="d-block border rounded-3 text-center p-3"
                                    style="cursor:pointer; border-style:dashed !important; border-color:#dee2e6; background:#f8f9fa;">

                                    @if ($photo_done)
                                    @php $isPdf = $photo_done->getMimeType() === 'application/pdf'; @endphp

                                    @if ($isPdf)
                                    <i class="ti ti-file-type-pdf text-danger" style="font-size:2.5rem"></i>
                                    <p class="text-success mb-0" style="font-size:11px">
                                        <i class="ti ti-check me-1"></i>{{ $photo_done->getClientOriginalName() }}
                                    </p>
                                    @else
                                    <img src="{{ $photo_done->temporaryUrl() }}"
                                        class="img-fluid rounded-2 shadow-sm mb-1"
                                        style="max-height:150px; object-fit:cover;">
                                    <p class="text-success mb-0" style="font-size:11px">
                                        <i class="ti ti-check me-1"></i>{{ $photo_done->getClientOriginalName() }}
                                    </p>
                                    @endif
                                    @else
                                    <i class="ti ti-cloud-upload text-muted" style="font-size:2rem"></i>
                                    <p class="mb-0 text-muted mt-1" style="font-size:12px">Klik untuk pilih foto/PDF</p>
                                    @endif

                                    <input id="photoDoneInput"
                                        type="file"
                                        wire:model="photo_done"
                                        accept="image/*,application/pdf"
                                        class="d-none">
                                </label>

                                <div wire:loading wire:target="photo_done" class="mt-1 text-primary" style="font-size:11px">
                                    <i class="ti ti-loader-2 me-1"></i>Mengupload...
                                </div>
                                @error('photo_done')
                                <div class="text-danger mt-1" style="font-size:11px">
                                    <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>

                            {{-- Pilih Pengantar --}}
                            <div class="mb-2">
                                <label class="form-label fw-semibold" style="font-size:12px">
                                    <i class="ti ti-user-check me-1"></i>Pengantar <span class="text-danger">*</span>
                                </label>
                                <select wire:model="pengantar_user_id" class="form-select form-select-sm">
                                    <option value="">-- Pilih Pengantar --</option>
                                    @foreach ($pengantarUsers as $u)
                                    <option value="{{ $u->user_id }}">{{ ucwords($u->nama_karyawan) }}</option>
                                    @endforeach
                                </select>
                                @error('pengantar_user_id')
                                <div class="text-danger mt-1" style="font-size:11px">
                                    <i class="ti ti-alert-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>

                        </div>

                        <div class="modal-footer border-0 px-4 pb-4 pt-2 gap-2">
                            <button type="button" wire:click="simpanDoneModal"
                                class="btn btn-sm btn-success"
                                wire:loading.attr="disabled"
                                wire:target="simpanDoneModal">
                                <span wire:loading.remove wire:target="simpanDoneModal">
                                    <i class="ti ti-check me-1"></i>Selesaikan Dokumen
                                </span>
                                <span wire:loading wire:target="simpanDoneModal">
                                    <i class="ti ti-loader-2 me-1"></i>Menyimpan...
                                </span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
            @endif

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
                                    <th class="small text-muted fw-semibold">Tujuan</th>
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
                                'unprocessed' => ['bg-warning-subtle text-warning-emphasis', 'Unprocessed'],
                                'onprocess' => ['bg-primary-subtle text-primary-emphasis', 'Onprocess'],
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
                                            ->first(fn($route) => !in_array($route->status, ['none', 'approved']));
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
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <i class="ti ti-inbox fs-2 d-block mb-2"></i>
                                            Belum ada dokumen.
                                        </td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="py-4 px-3">
                        <div class="flex">
                            <div class="flex space-x-4 items-center mb-3">
                                <label class="w-32 text-sm font-medium text-gray-900">Per Page</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        {{ $documents->links('livewire::bootstrap') }}
                    </div>
                </div>

                {{-- Detail Progress --}}
                @if ($selectedDocument)
                @php $steps = $selectedDocument->documentRoute; @endphp
                <div class="col-lg-5">
                    <div class="card h-100 shadow-sm">
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
                                'revisi' => 'danger',
                                'hilang' => 'danger',
                                'onprocess' => 'primary',
                                'none' => 'secondary',
                                'unprocessed' => 'warning',
                                default => 'secondary',
                                };
                                $badgeClass = match($step->status) {
                                'approved' => 'bg-success-subtle text-success-emphasis',
                                'rejected' => 'bg-danger-subtle text-danger-emphasis',
                                'revisi' => 'bg-danger-subtle text-danger-emphasis',
                                'hilang' => 'bg-dark-subtle text-dark-emphasis',
                                'onprocess' => 'bg-primary-subtle text-primary-emphasis',
                                'none' => 'bg-secondary-subtle text-secondary-emphasis',
                                'unprocessed' => 'bg-warning-subtle text-warning-emphasis',
                                default => 'bg-secondary-subtle text-secondary-emphasis',
                                };
                                $stepStatusLabel = match($step->status) {
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'revisi' => 'Revisi',
                                'hilang' => 'Hilang',
                                'onprocess' => 'Onprocess',
                                'none' => 'None',
                                default => ucfirst($step->status),
                                };
                                $isLastStep = $step->urutan == $steps->max('urutan');
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
                                                    {{ $stepStatusLabel }}
                                                </span>
                                                @if ($step->note)
                                                <div class="text-muted mt-1" style="font-size:11px">
                                                    Note : "{{ $step->note }}"
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

                                        @php
                                        $hasPreviousRevisi = collect($steps)
                                        ->take($index)
                                        ->contains(fn($item) => $item->status === 'revisi');

                                        $canEdit = in_array($step->status, ['unprocessed', 'onprocess']) &&
                                        !$hasPreviousRevisi;
                                        @endphp

                                        @if ($editingStepId == $step->document_route_id)
                                        <div class="mt-2 p-2 rounded border bg-light">
                                            <div class="mb-2">
                                                <label class="form-label mb-1" style="font-size:11px; font-weight:600;">Status</label>
                                                <select wire:model="editStatus" class="form-select form-select-sm">
                                                    <option value="unprocessed">Unprocessed</option>
                                                    <option value="onprocess">Onprocess</option>
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
                                                    style="font-size:11px; padding:2px 8px;">Batal</button>
                                                <button
                                                    wire:click="simpanEditStatus"
                                                    class="btn btn-sm btn-primary"
                                                    style="font-size:11px; padding:2px 8px;">
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