    <?php

    use App\Models\Departement;
    use App\Models\Document;
    use App\Models\DocumentRoute;
    use App\Models\User;
    use Illuminate\Support\Facades\Auth;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Component;

    new class extends Component
    {
        public $karyawan_nonoffice = [];
        public $departement =  [];
        public $selectedDepartements = [''];

        public $judul_dokumen;
        public $priority;
        public $assigned_to;

        public function mount()
        {
            $this->karyawan_nonoffice = User::where('role_id', 3)->get();
            $this->departement = Departement::all();
        }

        public function updatedSelectedDepartements()
        {
            $last = end($this->selectedDepartements);

            // Jika dropdown terakhir sudah dipilih,
            // tambahkan dropdown kosong baru
            if (!empty($last)) {
                $this->selectedDepartements[] = '';
            }
        }

        public function daftarin()
        {
            $message = [
                'judul_dokumen.required' => 'judul_dokumen wajib diisi',
                'judul_dokumen.min' => 'judul_dokumen minimal 3 karakter',
                'judul_dokumen.max' => 'judul_dokumen maksimal 100 karakter',
                'priority.required' => 'priority wajib diisi',
                'assigned_to.required' => 'Wajib pilih karyawan',
                'selectedDepartements.required' => 'Minimal pilih 1 departemen',
            ];

            $this->validate([
                'judul_dokumen' => ['required', 'min:3', 'max:100'],
                'priority' => 'required',
                'assigned_to' => 'required',
                'selectedDepartements' => ['required', function ($attribute, $value, $fail) {
                    $filtered = array_filter($value);
                    if (count($filtered) < 1) {
                        $fail('Minimal pilih 1 departemen tujuan.');
                    }
                }],
            ], $message);

            $document = Document::create([
                'judul_dokumen' => strtolower($this->judul_dokumen),
                'priority' => $this->priority,
                'assigned_to' => $this->assigned_to,
                'created_by' => auth()->id(),
                'current_status' => 'pending',
            ]);

            // Filter departemen yang dipilih (buang yang kosong)
            $departements = array_values(array_filter($this->selectedDepartements));

            // Insert document_routes
            foreach ($departements as $index => $departement_id) {
                DocumentRoute::create([
                    'document_id' => $document->document_id,
                    'departement_id' => $departement_id,
                    'urutan' => $index + 1,
                    'revision' => 1,
                    'status' => $index === 0 ? 'pending' : 'waiting',
                    'assigned_to' => null,
                    'notes' => null,
                ]);
            }

            $this->reset(['judul_dokumen', 'priority', 'assigned_to', 'selectedDepartements']);
            $this->selectedDepartements = [''];

            LivewireAlert::title('Berhasil Create')
                ->text('Berhasil membuat pengajuan dokumen')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();

            $this->dispatch('akun-created')->to('office.pengajuan.index');
        }
    };
    ?>

    <div
        wire:ignore.self
        class="modal fade"
        id="tambahDokumenModal"
        tabindex="-1"
        aria-labelledby="tambahDokumenModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDokumenModalLabel">
                        Tambah Dokumen
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
                        <label class="form-label">
                            Judul Dokumen
                        </label>

                        <input
                            type="text"
                            wire:model="judul_dokumen"
                            class="form-control"
                            placeholder="Masukkan judul dokumen">

                        @error('judul_dokumen')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Pengantar Dokumen
                        </label>
                        <select
                            wire:model.change="priority"
                            class="form-select">

                            <option value="">
                                -- Pilih Tingkat Prioritas --
                            </option>
                            <option value="mendesak">
                                {{ "Mendesak" }}
                            </option>
                            <option value="penting">
                                {{ "Penting" }}
                            </option>
                            <option value="normal">
                                {{ "Normal" }}
                            </option>
                        </select>

                        @error('priority')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Pengantar Dokumen
                        </label>

                        <select
                            wire:model.change="assigned_to"
                            class="form-select">

                            <option value="">
                                -- Pilih Karyawan --
                            </option>

                            @foreach ($karyawan_nonoffice as $nonoffice)
                            <option value="{{ $nonoffice->user_id }}">
                                {{ $nonoffice->nama_karyawan }}
                            </option>
                            @endforeach
                        </select>

                        @error('role_id')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            Departemen Tujuan
                        </label>

                        @foreach ($this->selectedDepartements as $index => $selected)
                        <div class="mb-2" wire:key="dept-{{ $index }}">
                            <select
                                wire:model.live="selectedDepartements.{{ $index }}"
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
                        </div>
                        @endforeach
                    </div>


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
                        class="btn btn-primary">
                        Simpan
                    </button>
                </div>

            </div>
        </div>
    </div>