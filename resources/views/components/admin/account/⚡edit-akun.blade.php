<?php

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    public $user_id;
    public $nama_karyawan;
    public $password;
    public $role_id;

    public $roles = [];

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    public function mount()
    {
        $this->roles = Role::all();
    }

    #[On('open-edit-akun')]
    public function editAkun($user_id)
    {
        $this->roles = Role::all();

        $user = User::find($user_id);

        $this->user_id = $user->user_id;
        $this->nama_karyawan = $user->nama_karyawan;
        $this->role_id = $user->role_id;


        $this->dispatch('show-edit-modal');
    }

    public function save()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }


    public function akunUpdate()
    {
        $message = [
            'nama_karyawan.required' => 'nama_karyawan wajib diisi',
            'nama_karyawan.min' => 'nama_karyawan minimal 3 karakter',
            'nama_karyawan.max' => 'nama_karyawan maksimal 100 karakter',
            'role_id.required' => 'Role wajib diisi',
        ];

        $this->validate([
            'nama_karyawan' => ['required', 'string', 'min:3', 'max:100'],
            'role_id' => 'required',
        ], $message);

        $user = User::find($this->user_id);
        // dd($this->status);
        if (!$user) {
            $this->dispatch('sweet-alert', icon: 'error', title: 'User tidak ditemukan');
            return;
        }

        if ($user) {
            $user->update([
                'nama_karyawan' => $this->nama_karyawan,
                'role_id' => $this->role_id,
            ]);


            $this->dispatch('hide-edit-modal');
            $this->reset();
            $this->dispatch('success');
            LivewireAlert::title('Berhasil Edit')
                ->text('Berhasil edit data karyawan')
                ->success()
                ->timer(3000)
                ->toast()
                ->position('top-end')
                ->show();
        }
    }
};
?>

<div>
    <div class="modal fade" id="editAkunModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAkunModal">
                        <strong>Edit Akun</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="akunUpdate">
                        <div class=" mb-3">
                            <label for="exampleInputNamaKaryawan1" class="form-label">Nama Karyawan</label>
                            <input wire:model="nama_karyawan" type="text" class="form-control" id="nama_karyawan"
                                aria-describedby="NamaKaryawanHelp">
                            @error('nama_karyawan')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select wire:model="role_id" class="form-select" id="role"
                                aria-label="Default select example">
                                <option value="" selected>Role</option>
                                @foreach ($roles as $role)
                                <option value="{{ $role->role_id }}">
                                    {{ $role->nama_role }}
                                </option>
                                @endforeach
                            </select>
                            @error('role')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                wire:click="closeModal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Livewire.on('hide-edit-modal', () => {
        const modal = bootstrap.Modal.getInstance(
            document.getElementById('editAkunModal')
        );

        modal?.hide();
    });
</script>