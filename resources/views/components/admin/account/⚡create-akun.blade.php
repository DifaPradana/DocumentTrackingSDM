    <?php

    use App\Models\Role;
    use App\Models\User;
    use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
    use Livewire\Component;

    new class extends Component
    {
        public $roles = [];

        public $nama_karyawan;
        public $password;
        public $role_id;

        public function mount()
        {
            $this->roles = Role::all();
        }

        public function daftarin()
        {
            $message = [
                'nama_karyawan.required' => 'nama_karyawan wajib diisi',
                'nama_karyawan.min' => 'nama_karyawan minimal 3 karakter',
                'nama_karyawan.max' => 'nama_karyawan maksimal 100 karakter',
                'password.required' => 'Password wajib diisi',
                'role_id.required' => 'Role wajib diisi',
            ];



            $this->validate([
                'nama_karyawan' => ['required', 'min:3', 'max:100'],
                'password' => 'required',
                'role_id' => 'required',
            ], $message);

            // dd($this->all());

            User::create([
                'nama_karyawan' => strtolower($this->nama_karyawan),
                'password' => $this->password,
                'role_id' => $this->role_id,
            ]);

            $this->reset(['nama_karyawan', 'password', 'role_id']);

            LivewireAlert::title('Berhasil Create')
                ->text('Berhasil create data karyawan')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();

            $this->dispatch('akun-created')->to('admin.account.index');
            // dd('event dikirim');

            // $this->dispatch('sweet-alert', icon: 'success', title: 'Kamu berhasil mendaftarkan akun baru');
        }
    };
    ?>

    <div
        wire:ignore.self
        class="modal fade"
        id="tambahAkunModal"
        tabindex="-1"
        aria-labelledby="tambahAkunModalLabel"
        aria-hidden="true">

        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tambahAkunModalLabel">
                        Tambah Akun
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
                            Nama Karyawan
                        </label>

                        <input
                            type="text"
                            wire:model="nama_karyawan"
                            class="form-control"
                            placeholder="Masukkan nama karyawan">

                        @error('nama_karyawan')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            wire:model="password"
                            class="form-control"
                            placeholder="Masukkan password">

                        @error('password')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Role
                        </label>

                        <select
                            wire:model.change="role_id"
                            class="form-select">

                            <option value="">
                                -- Pilih Role --
                            </option>

                            @foreach ($roles as $role)
                            <option value="{{ $role->role_id }}">
                                {{ $role->nama_role }}
                            </option>
                            @endforeach
                        </select>

                        @error('role_id')
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