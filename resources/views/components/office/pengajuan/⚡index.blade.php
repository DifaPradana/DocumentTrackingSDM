<?php

use App\Models\Document;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    public function render()
    {
        return $this->view()->layout('layouts.main')
            ->title('DocTracker | Pengajuan');
    }

    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public $perPage = 20;
    public $search = '';

    public function with(): array
    {
        return [
            'recentDocuments' => Document::query()
                ->whereHas('creator')
                ->with(['creator', 'assignee'])
                ->when(
                    $this->search,
                    fn($q) =>
                    $q->where('judul_dokumen', 'like', "%{$this->search}%")
                )
                ->latest()
                ->paginate($this->perPage),
        ];
    }

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('document-created')]
    public function refreshData() {}

    public function delete(Document $document)
    {
        $document->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete dokumen')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function editDokumen($document_id)
    {
        $this->dispatch('open-edit-document', document_id: $document_id);
        // dd("Kirim dispatch + $role_id");
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
                    <button wire:click="$dispatch('open-create-document')" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i> Ajukan Dokumen
                    </button>
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
            <livewire:office.pengajuan.create-pengajuan />
            <livewire:office.pengajuan.edit-pengajuan />
            <livewire:office.pengajuan.handle-revisi />
            <livewire:office.pengajuan.handle-hilang />
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th class="small text-muted fw-semibold">Judul</th>
                            <th class="small text-muted fw-semibold">PIC</th>
                            <th class="small text-muted fw-semibold">Router</th>
                            <th class="small text-muted fw-semibold">Prioritas</th>
                            <th class="small text-muted fw-semibold">Status</th>
                            <th class="small text-muted fw-semibold">Dibuat</th>
                            <th class="small text-muted fw-semibold">Deadline</th>
                            <th class="small text-muted fw-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentDocuments as $doc)
                        <tr class="text-center align-middle">
                            <td>
                                <span class="fw-medium">{{ ucwords($doc->judul_dokumen) }}</span>
                            </td>
                            <td>{{ ucwords($doc->creator->nama_karyawan ?? '-') }}</td>
                            <td>{{ ucwords($doc->assignee->nama_karyawan ?? '-') }}</td>
                            <td>
                                @php
                                $priorityMap = [
                                'tinggi' => ['bg-danger-subtle text-danger-emphasis', 'Tinggi'],
                                'sedang' => ['bg-warning-subtle text-warning-emphasis', 'Sedang'],
                                'rendah' => ['bg-secondary-subtle text-secondary-emphasis', 'Rendah'],
                                ];
                                [$priorityClass, $priorityLabel] = $priorityMap[$doc->priority] ?? ['bg-secondary-subtle text-secondary-emphasis', $doc->priority];
                                @endphp
                                <span class="badge {{ $priorityClass }}">{{ ucwords($priorityLabel) }}</span>
                            </td>
                            <td>
                                @php
                                $statusMap = [
                                'none' => ['bg-dark-subtle text-dark-emphasis', 'None'],
                                'onprocess' => ['bg-primary-subtle text-primary-emphasis', 'Onprocess'],
                                'unprocessed' => ['bg-warning-subtle text-warning-emphasis', 'Unprocessed'],
                                'selesai' => ['bg-success-subtle text-success-emphasis', 'Selesai'],
                                'revisi' => ['bg-danger-subtle text-danger-emphasis', 'Revisi'],
                                'hilang' => ['bg-dark-subtle text-dark-emphasis', 'Hilang'],
                                ];
                                [$statusClass, $statusLabel] = $statusMap[$doc->current_status] ?? ['bg-secondary-subtle text-secondary-emphasis', $doc->current_status];
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $doc->created_at->format('d M Y') }}</span>
                            </td>
                            <td>
                                <span class="small text-muted">{{ $doc->deadline->format('d M Y') }}</span>
                            </td>
                            <td class="border px-4 py-3 text-center text-black">
                                @if ($doc->current_status != 'selesai')
                                <button
                                    type="button"
                                    wire:click="editDokumen({{ $doc->document_id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50"
                                    wire:target="editDokumen({{ $doc->document_id }})"
                                    class="btn btn-warning m-1">
                                    <span wire:loading.remove wire:target="editDokumen({{ $doc->document_id }})">
                                        <i class="ti ti-pencil"></i>
                                    </span>
                                    <span wire:loading wire:target="editDokumen({{ $doc->document_id }})">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                    </span>
                                </button>
                                <button
                                    onclick="confirm('Kamu akan menghapus dokumen {{ $doc->judul_dokumen }} secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                    wire:click="delete({{ $doc->document_id }})"
                                    class="btn btn-danger m-1">
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                </button>
                                @endif
                                @if ($doc->current_status == 'revisi')
                                <button
                                    type="button"
                                    wire:click="$dispatch('open-handle-revisi', { document_id: {{ $doc->document_id }} })"
                                    class="btn btn-info m-1">
                                    <i class="ti ti-refresh"></i>
                                </button>
                                @endif
                                @if ($doc->current_status == 'hilang')
                                <button
                                    type="button"
                                    wire:click="$dispatch('open-handle-hilang', { document_id: {{ $doc->document_id }} })"
                                    class="btn btn-info m-1">
                                    <i class="ti ti-refresh"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="ti ti-inbox fs-4 d-block mb-1"></i>
                                Belum ada dokumen
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
                {{ $recentDocuments->links('livewire::bootstrap') }}
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-edit-modal', () => {
                const modalEl = document.getElementById('editDocumentModal');

                let modal = bootstrap.Modal.getInstance(modalEl);

                if (!modal) {
                    modal = new bootstrap.Modal(modalEl);
                }

                if (!modalEl.classList.contains('show')) {
                    modal.show();
                }
            });

            Livewire.on('hide-edit-modal', () => {
                const modalEl = document.getElementById('editDocumentModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal?.hide();
            });
        });

        // Fix overlay gelap: cleanup setiap kali modal selesai ditutup
        document.getElementById('editDocumentModal').addEventListener('hidden.bs.modal', () => {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        });
    </script>
</div>