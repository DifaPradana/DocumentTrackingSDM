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
                ->where('current_status', '!=', 'done')
                ->latest()
                ->take(10)
                ->get(),
        ];
    }
};
?>


<div wire:poll.30s class="col-lg-12 d-flex align-items-stretch mt-4">
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
                            <th class="small text-muted fw-semibold">Photo Dokumen</th>
                            <th class="small text-muted fw-semibold">PIC</th>
                            <th class="small text-muted fw-semibold">Router</th>
                            <th class="small text-muted fw-semibold">Prioritas</th>
                            <th class="small text-muted fw-semibold">Status</th>
                            <th class="small text-muted fw-semibold">Dibuat</th>
                            <th class="small text-muted fw-semibold">Deadline</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse ($recentDocuments as $doc)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $doc->judul_dokumen }}</span>
                            </td>
                            <td>
                                @if($doc->photo_start)
                                <div>
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalDokumen{{ $doc->document_id }}">
                                        <i class="ti ti-eye"></i>
                                        Lihat Dokumen
                                    </button>
                                </div>
                                @endif
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
                                'unprocessed' => ['bg-warning-subtle text-warning-emphasis', 'Unprocessed'],
                                'onprocess' => ['bg-primary-subtle text-primary-emphasis', 'Onprocess'],
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
                                <span class="small text-danger fw-bold">
                                    {{ $doc->deadline->format('d M Y') }}
                                </span>
                            </td>
                        </tr>

                        {{-- Modal Dokumen --}}
                        @if($doc->photo_start)

                        <div
                            class="modal fade"
                            id="modalDokumen{{ $doc->document_id }}"
                            tabindex="-1"
                            aria-hidden="true"
                            wire:ignore.self>

                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">

                                    <div class="modal-body text-center">

                                        @php
                                        $ext = strtolower(pathinfo($doc->photo_start, PATHINFO_EXTENSION));
                                        @endphp

                                        @if(in_array($ext, ['jpg','jpeg','png','webp']))
                                        <img
                                            src="{{ asset('storage/'.$doc->photo_start) }}"
                                            class="img-fluid rounded">
                                        @elseif($ext === 'pdf')
                                        <iframe
                                            src="{{ asset('storage/'.$doc->photo_start) }}"
                                            width="100%"
                                            height="600">
                                        </iframe>
                                        @else
                                        <a
                                            href="{{ asset('storage/'.$doc->photo_start) }}"
                                            target="_blank"
                                            class="btn btn-primary">

                                            Download Dokumen

                                        </a>
                                        @endif

                                    </div>

                                    <div class="modal-footer">
                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal">

                                            Tutup

                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endif
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