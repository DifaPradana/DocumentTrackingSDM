    <?php

    use App\Models\Role;
    use App\Models\User;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Component;

    new class extends Component
    {
        // public $roles = [];

        public $nama_role;
        public $jadwal_masuk;
        public $jadwal_pulang;
        public $role_id;

        public function daftarin()
        {
            $message = [
                'nama_role.required' => 'Nama Role wajib diisi',
                'nama_role.min' => 'Nama role minimal 3 karakter',
                'nama_role.max' => 'Nama role maksimal 100 karakter',
            ];



            $this->validate([
                'nama_role' => ['required', 'min:3', 'max:100'],
            ], $message);

            Role::create([
                'nama_role' => $this->nama_role,
            ]);

            $this->reset(['nama_role']);

            $this->dispatch('akun-created')->to('admin.role.index');

            LivewireAlert::title('Berhasil Create Role')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();
            // dd('event dikirim');

            // $this->dispatch('sweet-alert', icon: 'success', title: 'Kamu berhasil mendaftarkan akun baru');
        }
    };
    ?>

    <div
        wire:ignore.self
        class="modal fade"
        id="tambahRoleModal"
        tabindex="-1"
        aria-labelledby="tambahRoleModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tambahRoleModalLabel">
                        Tambah Role
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
                            Nama Role
                        </label>

                        <input
                            type="text"
                            wire:model="nama_role"
                            class="form-control"
                            placeholder="Masukkan nama role">

                        @error('nama_role')
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