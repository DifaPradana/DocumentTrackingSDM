    <?php

    use App\Models\Departement;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Component;

    new class extends Component
    {
        public $nama_departement;

        public function daftarin()
        {
            $message = [
                'nama_departement.required' => 'nama_departement wajib diisi',
                'nama_departement.max' => 'nama_departement maksimal 100 karakter',
            ];



            $this->validate([
                'nama_departement' => ['required', 'max:100'],
            ], $message);

            // dd($this->all());

            Departement::create([
                'nama_departement' => strtolower($this->nama_departement),
            ]);

            $this->reset(['nama_departement']);

            LivewireAlert::title('Berhasil Create')
                ->text('Berhasil create data departement')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();

            $this->dispatch('departement-created')->to('admin.departement.index');
            // dd('event dikirim');

            // $this->dispatch('sweet-alert', icon: 'success', title: 'Kamu berhasil mendaftarkan Departement baru');
        }
    };
    ?>

    <div
        wire:ignore.self
        class="modal fade"
        id="tambahDepartementModal"
        tabindex="-1"
        aria-labelledby="tambahDepartementModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDepartementModalLabel">
                        Tambah Departement
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
                            Nama Departement
                        </label>

                        <input
                            type="text"
                            wire:model="nama_departement"
                            class="form-control"
                            placeholder="Masukkan nama departement">

                        @error('nama_departement')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
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