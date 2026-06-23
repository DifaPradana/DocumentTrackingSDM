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
    public $aksi = null; // 'ulang' | 'close'

    #[On('open-handle-hilang')]
    public function openModal($document_id)
    {
        $this->document_id = $document_id;
        $this->document = Document::find($document_id);

        $this->routes = DocumentRoute::where('document_id', $document_id)
            ->with('departement')
            ->orderBy('urutan')
            ->get();

        $this->selectedUrutan = null;
        $this->aksi = null;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['document_id', 'document', 'routes', 'selectedUrutan', 'aksi']);
    }

    public function handleSubmit()
    {
        $this->validate([
            'aksi' => 'required|in:ulang,close',
        ], [
            'aksi.required' => 'Pilih salah satu tindakan',
        ]);

        if ($this->aksi === 'ulang') {
            $this->validate([
                'selectedUrutan' => 'required|integer|min:1',
            ], [
                'selectedUrutan.required' => 'Pilih step yang akan diulang',
            ]);

            DocumentRoute::where('document_id', $this->document_id)
                ->where('urutan', $this->selectedUrutan)
                ->update(['status' => 'unprocessed', 'note' => null]);

            DocumentRoute::where('document_id', $this->document_id)
                ->where('urutan', '>', $this->selectedUrutan)
                ->update(['status' => 'none', 'note' => null]);

            Document::where('document_id', $this->document_id)
                ->update(['current_status' => 'unprocessed']);

            $message = 'Dokumen dikembalikan ke step ' . $this->selectedUrutan;
        } elseif ($this->aksi === 'close') {
            Document::where('document_id', $this->document_id)
                ->update(['current_status' => 'closed']);

            $message = 'Dokumen ditutup (case close)';
        }

        $this->closeModal();

        LivewireAlert::title('Berhasil')
            ->text($message)
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
                    <h5 class="modal-title fw-semibold">Handle Hilang</h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    @if ($document)
                    <p class="text-muted mb-3">
                        Dokumen <strong>{{ ucwords($document->judul_dokumen) }}</strong>
                        sedang dalam status hilang. Pilih tindakan yang akan dilakukan:
                    </p>

                    {{-- Stepper --}}
                    <div class="d-flex align-items-start mb-4 flex-wrap gap-1">
                        @foreach ($routes as $route)
                        @php
                        $stepColor = match($route->status) {
                        'approved' => 'success',
                        'hilang' => 'danger',
                        'none' => 'primary',
                        default => 'secondary',
                        };
                        $stepIcon = match($route->status) {
                        'approved' => '✓',
                        'hilang' => '!',
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
                        <div style="height:2px;flex:1;min-width:16px;background:#dee2e6;margin-top:15px;flex-shrink:0"></div>
                        @endif

                        @endforeach
                    </div>

                    {{-- Pilihan Aksi (Radio) --}}
                    <div class="mb-3">
                        <label class="form-label fw-medium">Tindakan:</label>

                        <div class="form-check border rounded p-3 mb-2 {{ $aksi === 'ulang' ? 'border-primary bg-primary bg-opacity-10' : '' }}"
                            style="cursor:pointer"
                            wire:click="$set('aksi', 'ulang')">
                            <input class="form-check-input" type="radio"
                                wire:model="aksi"
                                value="ulang"
                                id="aksi_ulang">
                            <label class="form-check-label fw-medium" for="aksi_ulang" style="cursor:pointer">
                                Ulang di Step yang Hilang
                            </label>
                            <div class="text-muted" style="font-size:12px">
                                Dokumen dikembalikan dan diproses ulang dari step yang hilang
                            </div>
                        </div>

                        <div class="form-check border rounded p-3 {{ $aksi === 'close' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                            style="cursor:pointer"
                            wire:click="$set('aksi', 'close')">
                            <input class="form-check-input" type="radio"
                                wire:model="aksi"
                                value="close"
                                id="aksi_close">
                            <label class="form-check-label fw-medium text-danger" for="aksi_close" style="cursor:pointer">
                                Case Close
                            </label>
                            <div class="text-muted" style="font-size:12px">
                                Dokumen ditutup karena tidak ditemukan
                            </div>
                        </div>

                        @error('aksi')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- Pilih Step (muncul hanya jika aksi = ulang) --}}
                    @if ($aksi === 'ulang')
                    <div class="mb-3">
                        <label class="form-label fw-medium">Kembalikan ke step:</label>
                        <select wire:model="selectedUrutan" class="form-select">
                            <option value="">-- Pilih Step --</option>
                            @foreach ($routes as $route)
                            @if ($route->status === 'hilang')
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

                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Batal</button>
                    <button type="button"
                        class="btn {{ $aksi === 'close' ? 'btn-danger' : 'btn-primary' }}"
                        wire:click="handleSubmit">
                        {{ $aksi === 'close' ? 'Tutup Dokumen' : 'Kirim Ulang' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>