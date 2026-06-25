<?php

use App\Models\Document;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view()
            ->layout('layouts.main')
            ->title('DocTracker | Dashboard');
    }

    public function with(): array
    {
        $statusCounts = Document::query()
            ->where('created_at', '>=', now()->subDays(30)) // ganti ini
            ->whereIn('current_status', ['unprocessed', 'onprocess', 'done', 'revisi'])
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        return [
            'totalUnprocessed' => $statusCounts['unprocessed'] ?? 0,
            'totalProcessed'   => $statusCounts['onprocess']   ?? 0,
            'totalDone'        => $statusCounts['done']     ?? 0,
            'totalRevision'    => $statusCounts['revisi']      ?? 0,
        ];
    }
};
?>

<div>
    <div class="col-lg-12 d-flex align-items-stretch">
        <div class="card w-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-4">
                    <i class="ti ti-clipboard-list fs-5 text-muted"></i>
                    <h5 class="card-title fw-semibold mb-0">Pengajuan Dokumen</h5>
                </div>

                <div class="row g-3 mb-3">

                    {{-- Belum diproses --}}
                    <div class="col-md-3">
                        <a class="text-decoration-none d-block p-3 rounded-3 border border-warning-subtle bg-warning-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #E49B0F !important;">
                            <i class="ti ti-clock-hour-4 text-warning fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-warning">{{ $totalUnprocessed }}</div>
                            <div class="small text-muted">Belum Diproses</div>
                            <span class="badge bg-warning-subtle text-warning-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>30 Hari Terakhir
                            </span>
                        </a>
                    </div>

                    {{-- Diproses --}}
                    <div class="col-md-3">
                        <a class="text-decoration-none d-block p-3 rounded-3 border border-primary-subtle bg-primary-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #0c39db !important;">
                            <i class="ti ti-loader text-primary fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-primary">{{ $totalProcessed }}</div>
                            <div class="small text-muted">Sedang Diproses</div>
                            <span class="badge bg-primary-subtle text-primary-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>30 Hari Terakhir
                            </span>
                        </a>
                    </div>

                    {{-- Selesai --}}
                    <div class="col-md-3">
                        <a class="text-decoration-none d-block p-3 rounded-3 border border-success-subtle bg-success-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #3B6D11 !important;">
                            <i class="ti ti-circle-check text-success fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-success">{{ $totalDone }}</div>
                            <div class="small text-muted">Selesai</div>
                            <span class="badge bg-success-subtle text-success-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>Bulan ini
                            </span>
                        </a>
                    </div>

                    {{-- Revisi --}}
                    <div class="col-md-3">
                        <a class="text-decoration-none d-block p-3 rounded-3 border border-danger-subtle bg-danger-subtle bg-opacity-25 stat-card"
                            style="border-left: 3px solid #A32D2D !important;">
                            <i class="ti ti-circle-x text-danger fs-5 mb-1 d-block"></i>
                            <div class="fw-semibold fs-4 text-danger">{{ $totalRevision }}</div>
                            <div class="small text-muted">Revisi</div>
                            <span class="badge bg-danger-subtle text-danger-emphasis mt-1 small">
                                <i class="ti ti-point-filled me-1" style="font-size:10px"></i>Bulan ini
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <livewire:admin.dashboard.show-recent-document />
</div>