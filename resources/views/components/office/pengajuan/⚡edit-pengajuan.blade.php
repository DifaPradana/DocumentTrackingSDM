    <?php

    use App\Models\Departement;
    use App\Models\Document;
    use App\Models\DocumentRoute;
    use App\Models\User;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Attributes\On;
    use Livewire\Component;

    new class extends Component
    {
        public $document_id;
        public $judul_dokumen;
        public $priority;
        public $assigned_to;
        public $deadline;
        public $showModal = false;

        public $karyawan_nonoffice = [];
        public $departement = [];
        public $selectedDepartements = [''];


        public function addDepartementSlot()
        {
            $last = end($this->selectedDepartements);

            if (!empty($last)) {
                $this->selectedDepartements[] = '';
            }
        }

        public function mount()
        {
            $this->loadData();
        }

        private function loadData()
        {
            $this->karyawan_nonoffice = User::where('role_id', 3)->get();
            $this->departement = Departement::all();
        }

        public function closeModal()
        {
            $this->showModal = false;
            $this->resetExcept(['karyawan_nonoffice', 'departement']);
            $this->resetValidation();
            $this->selectedDepartements = [''];
        }

        #[On('open-edit-document')]
        public function editDocument($document_id)
        {
            $this->loadData();

            $document = Document::find($document_id);
            if (!$document) return;

            $this->document_id = $document->document_id;
            $this->judul_dokumen = ucwords($document->judul_dokumen);
            $this->priority = $document->priority;
            $this->assigned_to = $document->assigned_to;
            $this->deadline = $document->deadline;

            $routes = DocumentRoute::where('document_id', $document_id)
                ->orderBy('urutan')
                ->pluck('departement_id')
                ->toArray();

            $this->selectedDepartements = array_merge($routes, ['']);

            $this->showModal = true; // ← langsung set true, tidak perlu dispatch JS
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

        public function dokumenUpdate()
        {
            $message = [
                'judul_dokumen.required' => 'Judul dokumen wajib diisi',
                'judul_dokumen.min'      => 'Judul dokumen minimal 3 karakter',
                'judul_dokumen.max'      => 'Judul dokumen maksimal 100 karakter',
                'priority.required'      => 'Priority wajib diisi',
                'assigned_to.required'   => 'Wajib pilih karyawan',
                'deadline.required'      => 'Deadline harus diisi',
            ];

            $this->validate([
                'judul_dokumen'       => ['required', 'min:3', 'max:100'],
                'priority'            => 'required',
                'assigned_to'         => 'required',
                'selectedDepartements' => ['required', function ($attribute, $value, $fail) {
                    $filtered = array_filter($value);
                    if (count($filtered) < 1) {
                        $fail('Minimal pilih 1 departemen tujuan.');
                    }
                }],
                'deadline' => 'required',
            ], $message);

            $document = Document::find($this->document_id);

            if (!$document) {
                $this->dispatch('sweet-alert', icon: 'error', title: 'Dokumen tidak ditemukan');
                return;
            }

            // Update document utama
            $document->update([
                'judul_dokumen' => strtolower($this->judul_dokumen),
                'priority'      => $this->priority,
                'assigned_to'   => $this->assigned_to,
                'deadline'      => $this->deadline,
            ]);

            // Filter departemen yang dipilih (buang yang kosong)
            // Filter departemen yang dipilih (buang yang kosong)
            $departements = array_values(array_filter($this->selectedDepartements));

            // Cek apakah departemen berubah
            $existingDepartements = DocumentRoute::where('document_id', $this->document_id)
                ->orderBy('urutan')
                ->pluck('departement_id')
                ->map(fn($id) => (string) $id)
                ->toArray();

            $newDepartements = array_map('strval', $departements);

            if ($existingDepartements !== $newDepartements) {
                // Hapus semua routes lama lalu insert ulang dengan urutan baru
                DocumentRoute::where('document_id', $this->document_id)->delete();

                foreach ($departements as $index => $departement_id) {
                    DocumentRoute::create([
                        'document_id'    => $this->document_id,
                        'departement_id' => $departement_id,
                        'urutan'         => $index + 1,
                        'revision'       => 1,
                        'status'         => $index === 0 ? 'unprocessed' : 'none',
                    ]);
                }
            }

            $this->showModal = false;
            $this->resetExcept(['karyawan_nonoffice', 'departement']);
            $this->selectedDepartements = [''];
            $this->dispatch('document-updated');

            LivewireAlert::title('Berhasil Edit')
                ->text('Berhasil edit dokumen')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();
        }
    };
    ?>

    <div>
        {{-- Overlay backdrop --}}
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
                        <h5 class="modal-title"><strong>Edit Dokumen</strong></h5>
                        <button type="button" class="btn-close"
                            wire:click="closeModal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form wire:submit.prevent="dokumenUpdate">

                            {{-- Judul Dokumen --}}
                            <div class="mb-3">
                                <label class="form-label">Judul Dokumen</label>
                                <input wire:model="judul_dokumen" type="text"
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
                                <div class="mb-2 d-flex align-items-center gap-2" wire:key="dept-edit-{{ $index }}">

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

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light"
                                    wire:click="closeModal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>