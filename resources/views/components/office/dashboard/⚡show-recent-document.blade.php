<?php

use App\Models\Document;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        return [
            'recentDocuments' => Document::query()
                ->whereHas('creator')
                ->with(['creator', 'assignee'])
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
};
?>


<div class="col-lg-12 d-flex align-items-stretch mt-4">
    <div class="card w-100">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="ti ti-file-text fs-5 text-muted"></i>
                <h5 class="card-title fw-semibold mb-0">Dokumen Terbaru</h5>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th class="small text-muted fw-semibold">Judul</th>
                            <th class="small text-muted fw-semibold">Pengaju</th>
                            <th class="small text-muted fw-semibold">Ditugaskan ke</th>
                            <th class="small text-muted fw-semibold">Prioritas</th>
                            <th class="small text-muted fw-semibold">Status</th>
                            <th class="small text-muted fw-semibold">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse ($recentDocuments as $doc)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $doc->judul_dokumen }}</span>
                            </td>
                            <td>{{ $doc->creator->nama_karyawan ?? '-' }}</td>
                            <td>{{ $doc->assignee->nama_karyawan ?? '-' }}</td>
                            <td>
                                @php
                                $priorityMap = [
                                'tinggi' => ['bg-danger-subtle text-danger-emphasis', 'Tinggi'],
                                'sedang' => ['bg-warning-subtle text-warning-emphasis', 'Sedang'],
                                'rendah' => ['bg-secondary-subtle text-secondary-emphasis', 'Rendah'],
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
                                'approved' => ['bg-success-subtle text-success-emphasis', 'Selesai'],
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

        </div>
    </div>
</div>