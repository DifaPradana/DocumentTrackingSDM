<?php

use App\Models\Departement;
use App\Models\SectionHead;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;

new class extends Component
{
    public $departement = [];

    public $nama_section_head;
    public $nama_pjs;
    public $tanggal_mulai_pjs;
    public $tanggal_akhir_pjs;
    public $departement_id;

    public function mount()
    {
        $this->departement = Departement::whereDoesntHave('sectionHead')->get();
    }

    public function updatedNamaPjs()
    {
        // Reset tanggal ketika nama_pjs dikosongkan
        if (empty($this->nama_pjs)) {
            $this->reset(['tanggal_mulai_pjs', 'tanggal_akhir_pjs']);
        }
    }

    public function daftarin()
    {
        $rules = [
            'nama_section_head' => ['required', 'min:3', 'max:100'],
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

        // Jika nama_pjs diisi, tanggal menjadi required
        if (!empty($this->nama_pjs)) {
            $rules['tanggal_mulai_pjs'] = ['required', 'date'];
            $rules['tanggal_akhir_pjs'] = ['required', 'date', 'after_or_equal:tanggal_mulai_pjs'];

            $messages['tanggal_mulai_pjs.required']      = 'Tanggal mulai wajib diisi jika nama PJS diisi';
            $messages['tanggal_mulai_pjs.date']          = 'Tanggal mulai harus berupa tanggal yang valid';
            $messages['tanggal_akhir_pjs.required']      = 'Tanggal akhir wajib diisi jika nama PJS diisi';
            $messages['tanggal_akhir_pjs.date']          = 'Tanggal akhir harus berupa tanggal yang valid';
            $messages['tanggal_akhir_pjs.after_or_equal'] = 'Tanggal akhir tidak boleh sebelum tanggal mulai';
        }

        $this->validate($rules, $messages);

        SectionHead::create([
            'nama_section_head' => $this->nama_section_head,
            'departement_id'    => $this->departement_id,
            'nama_pjs'          => !empty($this->nama_pjs) ? strtolower($this->nama_pjs) : null,
            'tanggal_mulai_pjs' => !empty($this->nama_pjs) ? $this->tanggal_mulai_pjs : null,
            'tanggal_akhir_pjs' => !empty($this->nama_pjs) ? $this->tanggal_akhir_pjs : null,
        ]);

        $this->reset(['nama_section_head', 'departement_id', 'nama_pjs', 'tanggal_mulai_pjs', 'tanggal_akhir_pjs']);

        LivewireAlert::title('Berhasil Create')
            ->text('Berhasil create data section head')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('success');
    }
};
?>

<div
    wire:ignore.self
    class="modal fade"
    id="tambahSectionHeadModal"
    tabindex="-1"
    aria-labelledby="tambahSectionHeadModalLabel"
    aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="tambahSectionHeadModalLabel">
                    Tambah Section Head
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Section Head</label>
                    <input
                        type="text"
                        wire:model="nama_section_head"
                        class="form-control @error('nama_section_head') is-invalid @enderror"
                        placeholder="Masukkan nama section head">
                    @error('nama_section_head')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Departement</label>
                    <select
                        wire:model.change="departement_id"
                        class="form-select @error('departement_id') is-invalid @enderror">
                        <option value="">-- Pilih Departement --</option>
                        @foreach ($departement as $dept)
                        <option value="{{ $dept->departement_id }}">
                            {{ $dept->nama_departement }}
                        </option>
                        @endforeach
                    </select>
                    @error('departement_id')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Nama PJS
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input
                        type="text"
                        wire:model.live="nama_pjs"
                        class="form-control @error('nama_pjs') is-invalid @enderror"
                        placeholder="Masukkan nama PJS">
                    @error('nama_pjs')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row g-2">
                    <div class="col-12 col-md-6">
                        <label for="tanggal_mulai_pjs" class="form-label fw-semibold">
                            Tanggal Mulai
                            @if($nama_pjs)
                            <span class="text-danger">*</span>
                            @else
                            <span class="text-muted fw-normal">(opsional)</span>
                            @endif
                        </label>
                        <input
                            type="date"
                            id="tanggal_mulai_pjs"
                            wire:model.live="tanggal_mulai_pjs"
                            class="form-control @error('tanggal_mulai_pjs') is-invalid @enderror"
                            @if(!$nama_pjs) disabled @endif>
                        @error('tanggal_mulai_pjs')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="tanggal_akhir_pjs" class="form-label fw-semibold">
                            Tanggal Akhir
                            @if($nama_pjs)
                            <span class="text-danger">*</span>
                            @else
                            <span class="text-muted fw-normal">(opsional)</span>
                            @endif
                        </label>
                        <input
                            type="date"
                            id="tanggal_akhir_pjs"
                            wire:model.live="tanggal_akhir_pjs"
                            class="form-control @error('tanggal_akhir_pjs') is-invalid @enderror"
                            min="{{ $tanggal_mulai_pjs }}"
                            @if(!$nama_pjs || !$tanggal_mulai_pjs) disabled @endif>
                        @error('tanggal_akhir_pjs')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                @if(!$nama_pjs)
                <small class="text-muted mt-1 d-block">
                    <i class="bi bi-info-circle"></i>
                    Isi Nama PJS untuk mengaktifkan field tanggal
                </small>
                @endif

            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="daftarin"
                    wire:loading.attr="disabled"
                    wire:target="daftarin"
                    class="btn btn-primary">
                    <span wire:loading.remove wire:target="daftarin">Simpan</span>
                    <span wire:loading wire:target="daftarin">
                        <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                        Menyimpan...
                    </span>
                </button>
            </div>

        </div>
    </div>
</div>