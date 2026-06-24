<?php

use App\Models\Departement;
use App\Models\Document;
use App\Models\DocumentRoute;
use App\Models\User;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Component;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;


new class extends Component
{
    use WithFileUploads;

    public $showModal = false;
    public $karyawan_nonoffice = [];
    public $departement = [];
    public $selectedDepartements = [''];
    public $photo_start;
    public $judul_dokumen;
    public $priority;
    public $assigned_to;
    public $deadline;

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        $this->karyawan_nonoffice = User::where('role_id', 3)->get();
        $this->departement = Departement::all();
    }

    #[On('open-create-document')]
    public function openCreateModal()
    {
        $this->loadData();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetExcept(['karyawan_nonoffice', 'departement']);
        $this->resetValidation();
        $this->selectedDepartements = [''];
    }

    public function addDepartementSlot()
    {
        $last = end($this->selectedDepartements);
        if (!empty($last)) {
            $this->selectedDepartements[] = '';
        }
    }

    public function removeDepartement($index)
    {
        unset($this->selectedDepartements[$index]);
        $this->selectedDepartements = array_values($this->selectedDepartements);

        $last = end($this->selectedDepartements);
        if (!empty($last)) {
            $this->selectedDepartements[] = '';
        }
    }

    public function daftarin()
    {
        $message = [
            'judul_dokumen.required' => 'Judul dokumen wajib diisi',
            'judul_dokumen.min'      => 'Judul dokumen minimal 3 karakter',
            'judul_dokumen.max'      => 'Judul dokumen maksimal 100 karakter',
            'priority.required'      => 'Priority wajib diisi',
            'assigned_to.required'   => 'Wajib pilih karyawan',
            'deadline.required'      => 'Deadline harus diisi',
            'photo_start.required'   => 'Harus upload photo/pdf dokumen, cover saja tidak apa-apa',
            'photo_start.mimes'      => 'Harus berupa photo/pdf',
            'photo_start.max'        => 'Ukuran maksimal 10 MB',
        ];

        $this->validate([
            'judul_dokumen'        => ['required', 'min:3', 'max:100'],
            'priority'             => 'required',
            'assigned_to'          => 'required',
            'selectedDepartements' => ['required', function ($attribute, $value, $fail) {
                $filtered = array_filter($value);
                if (count($filtered) < 1) {
                    $fail('Minimal pilih 1 departemen tujuan.');
                }
            }],
            'deadline' => 'required',
            'photo_start' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10000'

        ], $message);


        if ($this->photo_start) {

            $uploadedFile = $this->photo_start;

            $filename = Str::uuid() . '.' . $uploadedFile->extension();
            $relativePath = 'pengajuan-dokumen/' . $filename;
            $fullPath = storage_path('app/public/' . $relativePath);

            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $mimeType = $uploadedFile->getMimeType();

            if (str_starts_with($mimeType, 'image/')) {

                $manager = ImageManager::usingDriver(Driver::class);

                $image = $manager->decodeSplFileInfo($uploadedFile);

                // resize hanya jika lebih besar dari 1200px
                if ($image->width() > 1200) {
                    $image->scale(width: 1200);
                }

                $image->save($fullPath, quality: 70);
            } else {

                // PDF atau file lain langsung simpan
                copy(
                    $uploadedFile->getRealPath(),
                    $fullPath
                );
            }

            $dokumenPath = $relativePath;
        }

        $document = Document::create([
            'judul_dokumen'  => strtolower($this->judul_dokumen),
            'priority'       => $this->priority,
            'assigned_to'    => $this->assigned_to,
            'created_by'     => auth()->id(),
            'current_status' => 'unprocessed',
            'deadline'       => $this->deadline,
            'photo_start'    => $dokumenPath,
        ]);

        $departements = array_values(array_filter($this->selectedDepartements));

        foreach ($departements as $index => $departement_id) {
            DocumentRoute::create([
                'document_id'    => $document->document_id,
                'departement_id' => $departement_id,
                'urutan'         => $index + 1,
                'revision'       => 1,
                'status'         => $index === 0 ? 'unprocessed' : 'none',
            ]);
        }

        $this->showModal = false;
        $this->resetExcept(['karyawan_nonoffice', 'departement']);
        $this->selectedDepartements = [''];

        LivewireAlert::title('Berhasil Create')
            ->text('Berhasil membuat pengajuan dokumen')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('document-created')->to('office.pengajuan.index');
    }
};
?>

<div>
    {{-- <button wire:click="openModal" class="btn btn-primary">Ajukan Dokumen</button> --}}

    {{-- Backdrop --}}
    @if ($showModal)
    <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Modal --}}
    <div class="modal fade {{ $showModal ? 'show d-block' : '' }}"
        tabindex="-1"
        style="{{ $showModal ? 'z-index: 1055;' : '' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"><strong>Tambah Dokumen</strong></h5>
                    <button type="button" class="btn-close"
                        wire:click="closeModal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form wire:submit.prevent="daftarin">

                        {{-- Judul Dokumen --}}
                        <div class="mb-3">
                            <label class="form-label">Judul Dokumen</label>
                            <input type="text" wire:model="judul_dokumen"
                                class="form-control" placeholder="Masukkan judul dokumen">
                            @error('judul_dokumen')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Level Prioritas --}}
                        <div class="mb-3">
                            <label class="form-label">Level Prioritas</label>
                            <select wire:model="priority" class="form-select">
                                <option value="">-- Pilih Tingkat Prioritas --</option>
                                <option value="tinggi">Tinggi</option>
                                <option value="sedang">Sedang</option>
                                <option value="rendah">Rendah</option>
                            </select>
                            @error('priority')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Pengantar Dokumen --}}
                        <div class="mb-3">
                            <label class="form-label">Pengantar Dokumen</label>
                            <select wire:model="assigned_to" class="form-select">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($karyawan_nonoffice as $nonoffice)
                                <option value="{{ $nonoffice->user_id }}">
                                    {{ $nonoffice->nama_karyawan }}
                                </option>
                                @endforeach
                            </select>
                            @error('assigned_to')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Departemen Tujuan --}}
                        <div class="mb-3">
                            <label class="form-label">Departemen Tujuan</label>
                            <small class="text-muted d-block mb-2">
                                Urutan dari atas ke bawah menentukan urutan routing dokumen.
                            </small>

                            @foreach ($this->selectedDepartements as $index => $selected)
                            <div class="mb-2 d-flex align-items-center gap-2" wire:key="dept-{{ $index }}">

                                @if (!($index === count($this->selectedDepartements) - 1 && empty($selected)))
                                <span class="badge bg-secondary" style="min-width:28px">
                                    {{ $index + 1 }}
                                </span>
                                @else
                                <span style="min-width:28px"></span>
                                @endif

                                <select
                                    wire:model="selectedDepartements.{{ $index }}"
                                    wire:change="addDepartementSlot"
                                    class="form-select">
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departement as $dept)
                                    @if (
                                    !in_array($dept->departement_id, array_filter($this->selectedDepartements))
                                    || $selected == $dept->departement_id
                                    )
                                    <option value="{{ $dept->departement_id }}">
                                        {{ $dept->nama_departement }}
                                    </option>
                                    @endif
                                    @endforeach
                                </select>

                                @if (!($index === count($this->selectedDepartements) - 1 && empty($selected)))
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    wire:click="removeDepartement({{ $index }})">
                                    &times;
                                </button>
                                @endif
                            </div>
                            @endforeach

                            @error('selectedDepartements')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Deadline --}}
                        <div class="mb-3">
                            <label class="form-label">Deadline</label>
                            <input type="date" wire:model="deadline"
                                class="form-control" min="{{ now()->format('Y-m-d') }}">
                            @error('deadline')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                Foto Dokumen
                            </label>
                            <input type="file" wire:model="photo_start" accept="image/*, .pdf" capture="user" class="form-control" id="photo_start">
                            <div wire:loading wire:target="photo_start" class="text-primary mt-2">
                                <span class="spinner-border spinner-border-sm"></span>
                                Uploading...
                            </div>
                            @error('photo_start')
                            <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                wire:click="closeModal">Batal</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>