<?php

use App\Models\Departement;
use App\Models\SectionHead;
use Livewire\Attributes\On;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    public $section_head_id;
    public $nama_section_head;
    public $nama_pjs;
    public $tanggal_mulai_pjs;
    public $tanggal_akhir_pjs;
    public $departement_id;
    public $departements = [];

    public function mount()
    {
        $this->departements = Departement::all();
    }

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('open-edit-section-head')]
    public function editSectionHead($section_head_id)
    {
        $this->departements = Departement::all();

        $secHead = SectionHead::find($section_head_id);

        if (!$secHead) {
            LivewireAlert::title('Error')
                ->text('Section head tidak ditemukan')
                ->error()
                ->toast()
                ->position('top-end')
                ->timer(3000)
                ->show();
            return;
        }

        $this->section_head_id   = $secHead->section_head_id;
        $this->nama_section_head = $secHead->nama_section_head;
        $this->nama_pjs          = $secHead->nama_pjs;
        $this->tanggal_mulai_pjs = $secHead->tanggal_mulai_pjs?->format('Y-m-d');
        $this->tanggal_akhir_pjs = $secHead->tanggal_akhir_pjs?->format('Y-m-d');
        $this->departement_id    = $secHead->departement_id;

        $this->dispatch('show-edit-modal');
    }

    public function akunUpdate()
    {
        $rules = [
            'nama_section_head' => ['required', 'string', 'min:3', 'max:100'],
            'departement_id'    => ['required'],
            'nama_pjs'          => ['nullable', 'string', 'max:100'],
            'tanggal_mulai_pjs' => ['nullable'],
            'tanggal_akhir_pjs' => ['nullable'],
        ];

        $messages = [
            'nama_section_head.required' => 'Nama section head wajib diisi',
            'nama_section_head.min'      => 'Nama section head minimal 3 karakter',
            'nama_section_head.max'      => 'Nama section head maksimal 100 karakter',
            'departement_id.required'    => 'Departement wajib dipilih',
            'nama_pjs.max'               => 'Nama PJS maksimal 100 karakter',
        ];

        if (!empty($this->nama_pjs)) {
            $rules['tanggal_mulai_pjs'] = ['required', 'date'];
            $rules['tanggal_akhir_pjs'] = ['required', 'date', 'after_or_equal:tanggal_mulai_pjs'];

            $messages['tanggal_mulai_pjs.required']       = 'Tanggal mulai wajib diisi jika nama PJS diisi';
            $messages['tanggal_mulai_pjs.date']           = 'Tanggal mulai harus berupa tanggal yang valid';
            $messages['tanggal_akhir_pjs.required']       = 'Tanggal akhir wajib diisi jika nama PJS diisi';
            $messages['tanggal_akhir_pjs.date']           = 'Tanggal akhir harus berupa tanggal yang valid';
            $messages['tanggal_akhir_pjs.after_or_equal'] = 'Tanggal akhir tidak boleh sebelum tanggal mulai';
        }

        $this->validate($rules, $messages);

        $secHead = SectionHead::find($this->section_head_id);

        if (!$secHead) {
            LivewireAlert::title('Error')
                ->text('Section head tidak ditemukan')
                ->error()
                ->toast()
                ->position('top-end')
                ->timer(3000)
                ->show();
            return;
        }

        $secHead->update([
            'nama_section_head' => strtolower($this->nama_section_head),
            'departement_id'    => $this->departement_id,
            'nama_pjs'          => !empty($this->nama_pjs) ? strtolower($this->nama_pjs) : null,
            'tanggal_mulai_pjs' => !empty($this->nama_pjs) ? $this->tanggal_mulai_pjs : null,
            'tanggal_akhir_pjs' => !empty($this->nama_pjs) ? $this->tanggal_akhir_pjs : null,
        ]);

        $this->dispatch('hide-edit-modal');
        $this->reset();
        $this->dispatch('success');

        LivewireAlert::title('Berhasil Edit')
            ->text('Berhasil edit data section head')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();
    }
};
?>

<div>
    <div class="modal fade" id="editSectionHeadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><strong>Edit Section Head</strong></h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        wire:click="closeModal"
                        aria-label="Close">
                    </button>
                </div>

                <div class="modal-body">
                    {{-- wire:ignore mencegah Livewire re-render bagian ini saat ada update --}}
                    <div
                        wire:ignore
                        x-data="{
                            namaPjs: @entangle('nama_pjs'),
                            tanggalMulai: @entangle('tanggal_mulai_pjs'),
                            tanggalAkhir: @entangle('tanggal_akhir_pjs'),
                            get hasPjs() { return this.namaPjs && this.namaPjs.trim() !== '' },
                            clearTanggal() {
                                if (!this.hasPjs) {
                                    this.tanggalMulai = '';
                                    this.tanggalAkhir = '';
                                }
                            }
                        }">
                        {{-- Nama Section Head --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Section Head</label>
                            <input
                                type="text"
                                x-model.debounce="$wire.nama_section_head"
                                class="form-control"
                                placeholder="Masukkan nama section head">
                        </div>

                        {{-- Departement --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Departement</label>
                            <select
                                x-bind:value="$wire.departement_id"
                                x-on:change="$wire.set('departement_id', $event.target.value)"
                                class="form-select @error('departement_id') is-invalid @enderror">
                                <option value="">-- Pilih Departement --</option>
                                @foreach ($departements as $dept)
                                <option value="{{ $dept->departement_id }}">
                                    {{ $dept->nama_departement }}
                                </option>
                                @endforeach
                            </select>
                            @error('departement_id')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Nama PJS --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Nama PJS
                                <span class="text-muted fw-normal">(opsional)</span>
                            </label>
                            <input
                                type="text"
                                x-model="namaPjs"
                                x-on:input="clearTanggal()"
                                class="form-control"
                                placeholder="Masukkan nama PJS">
                        </div>

                        {{-- Tanggal PJS --}}
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Tanggal Mulai
                                    <span x-show="hasPjs" class="text-danger">*</span>
                                    <span x-show="!hasPjs" class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <input
                                    type="date"
                                    x-model="tanggalMulai"
                                    x-on:change="if(tanggalAkhir && tanggalAkhir < tanggalMulai) tanggalAkhir = ''"
                                    class="form-control"
                                    :disabled="!hasPjs">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">
                                    Tanggal Akhir
                                    <span x-show="hasPjs" class="text-danger">*</span>
                                    <span x-show="!hasPjs" class="text-muted fw-normal">(opsional)</span>
                                </label>
                                <input
                                    type="date"
                                    x-model="tanggalAkhir"
                                    class="form-control"
                                    :min="tanggalMulai"
                                    :disabled="!hasPjs || !tanggalMulai">
                            </div>
                        </div>

                        <small x-show="!hasPjs" class="text-muted mt-1 d-block">
                            <i class="bi bi-info-circle"></i>
                            Isi Nama PJS untuk mengaktifkan field tanggal
                        </small>
                    </div>

                    {{-- Error messages di luar wire:ignore agar tetap bisa dirender Livewire --}}
                    @error('nama_section_head')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                    @error('departement_id')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                    @error('nama_pjs')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                    @error('tanggal_mulai_pjs')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                    @error('tanggal_akhir_pjs')
                    <small class="text-danger d-block mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal"
                        wire:click="closeModal">
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="akunUpdate"
                        class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Livewire.on('hide-edit-modal', () => {
        const modal = bootstrap.Modal.getInstance(
            document.getElementById('editSectionHeadModal')
        );
        modal?.hide();
    });
</script>