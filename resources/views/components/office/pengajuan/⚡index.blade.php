<?php

use App\Models\Document;
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
                ->paginate($this->perPage), // ← bukan take()->get()
        ];
    }

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
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

                    <a href="#tambahDokumenModal"
                        data-bs-toggle="modal"
                        class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>
                        Ajukan Dokumen
                    </a>
                </div>
            </div>
            <br>
            <livewire:office.pengajuan.create-pengajuan />
            <livewire:office.pengajuan.edit-pengajuan />
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center align-middle">
                            <th class="small text-muted fw-semibold">Judul</th>
                            <th class="small text-muted fw-semibold">Pengaju</th>
                            <th class="small text-muted fw-semibold">Ditugaskan ke</th>
                            <th class="small text-muted fw-semibold">Prioritas</th>
                            <th class="small text-muted fw-semibold">Status</th>
                            <th class="small text-muted fw-semibold">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentDocuments as $doc)
                        <tr class="text-center align-middle">
                            <td>
                                <span class="fw-medium">{{ $doc->judul_dokumen }}</span>
                            </td>
                            <td>{{ $doc->creator->nama_karyawan ?? '-' }}</td>
                            <td>{{ $doc->assignee->nama_karyawan ?? '-' }}</td>
                            <td>
                                @php
                                $priorityMap = [
                                'urgent' => ['bg-danger-subtle text-danger-emphasis', 'Urgent'],
                                'penting' => ['bg-warning-subtle text-warning-emphasis', 'Penting'],
                                'normal' => ['bg-secondary-subtle text-secondary-emphasis', 'Normal'],
                                ];
                                [$priorityClass, $priorityLabel] = $priorityMap[$doc->priority] ?? ['bg-secondary-subtle text-secondary-emphasis', $doc->priority];
                                @endphp
                                <span class="badge {{ $priorityClass }}">{{ $priorityLabel }}</span>
                            </td>
                            <td>
                                @php
                                $statusMap = [
                                'pending' => ['bg-warning-subtle text-warning-emphasis', 'Pending'],
                                'waiting' => ['bg-primary-subtle text-primary-emphasis', 'Waiting'],
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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
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
                {{ $recentDocuments->links() }}
            </div>
        </div>
    </div>
</div>