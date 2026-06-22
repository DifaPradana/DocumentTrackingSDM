<?php

use Livewire\Component;
use App\Models\Document;
use App\Models\DocumentRoute;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;

new class extends Component
{
    public $showModal = false;
    public $document_id;
    public $document;
    public $routes = [];
    public $selectedUrutan;

    #[On('open-handle-revisi')]
    public function openModal($document_id)
    {
        $this->document_id = $document_id;
        $this->document = Document::find($document_id);

        // Ambil semua routes, termasuk yang revisi untuk ditampilkan
        $this->routes = DocumentRoute::where('document_id', $document_id)
            ->with('departement')
            ->orderBy('urutan')
            ->get();

        $this->selectedUrutan = null;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['document_id', 'document', 'routes', 'selectedUrutan']);
    }

    public function handleRevisi()
    {
        $this->validate([
            'selectedUrutan' => 'required|integer|min:1',
        ], [
            'selectedUrutan.required' => 'Pilih step tujuan revisi',
        ]);

        // Update step yang dipilih ke pending, step setelahnya ke waiting
        DocumentRoute::where('document_id', $this->document_id)
            ->where('urutan', $this->selectedUrutan)
            ->update(['status' => 'pending', 'note' => null]);

        DocumentRoute::where('document_id', $this->document_id)
            ->where('urutan', '>', $this->selectedUrutan)
            ->update(['status' => 'waiting', 'note' => null]);

        // Update current_status dokumen
        Document::where('document_id', $this->document_id)
            ->update(['current_status' => 'pending']);

        $this->closeModal();

        LivewireAlert::title('Berhasil')
            ->text('Dokumen dikembalikan ke step ' . $this->selectedUrutan)
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();

        $this->dispatch('document-created');
    }
};
?>

<div>
    @if ($showModal)
    <div class="modal-backdrop fade show"></div>
    @endif

    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}"
        tabindex="-1"
        style="{{ $showModal ? 'z-index: 1055;' : '' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold">Handle Revisi</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    @if ($document)
                    <p class="text-muted mb-3">
                        Dokumen <strong>{{ ucwords($document->judul_dokumen) }}</strong>
                        sedang dalam status revisi. Pilih step yang akan diulang:
                    </p>

                    {{-- Tampilkan stepper current state - tampilkan SEMUA step --}}
                    <div class="d-flex align-items-start mb-4 flex-wrap gap-1">
                        @foreach ($routes as $route)
                        @php
                        $stepColor = match($route->status) {
                        'approved' => 'success',
                        'revisi' => 'danger',
                        'pending' => 'primary',
                        default => 'secondary',
                        };
                        $stepIcon = match($route->status) {
                        'approved' => '✓',
                        'revisi' => '↩',
                        default => $route->urutan,
                        };
                        @endphp

                        <div class="d-flex flex-column align-items-center" style="min-width:70px;max-width:70px">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                style="width:32px;height:32px;background:var(--bs-{{ $stepColor }});font-size:13px;flex-shrink:0">
                                {{ $stepIcon }}
                            </div>
                            <small class="text-center mt-1" style="font-size:10px">
                                {{ $route->departement->nama_departement }}
                            </small>
                            @if ($route->note)
                            <small class="text-danger text-center fst-italic mt-1" style="font-size:9px">
                                "{{ $route->note }}"
                            </small>
                            @endif
                        </div>

                        @if (!$loop->last)
                        {{-- padding-top 16px = setengah tinggi circle (32px) supaya garis sejajar tengah circle --}}
                        <div style="height:2px;flex:1;min-width:16px;background:#dee2e6;padding-top:0;margin-top:15px;flex-shrink:0"></div>
                        @endif

                        @endforeach
                    </div>

                    {{-- Pilih step tujuan --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Kembalikan ke step:</label>
                        <select wire:model="selectedUrutan" class="form-select">
                            <option value="">-- Pilih Step --</option>
                            @foreach ($routes as $route)
                            {{-- Hanya tampilkan step yang bukan setelah revisi --}}
                            @if ($route->status !== 'waiting')
                            <option value="{{ $route->urutan }}">
                                Step {{ $route->urutan }} — {{ $route->departement->nama_departement }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                        @error('selectedUrutan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Batal</button>
                    <button type="button" class="btn btn-primary" wire:click="handleRevisi">
                        Kirim Ulang
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>