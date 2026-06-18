<?php

use App\Models\Role;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public $nama_role;
    public $role_id;

    public $roles = [];

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('open-edit-role')]
    public function editRole($role_id)
    {
        $role = Role::find($role_id);
        $this->role_id = $role->role_id;
        $this->nama_role = ucwords($role->nama_role);

        $this->dispatch('show-edit-modal');

        // dd("sampe sini");
    }

    public function save()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }


    public function roleUpdate()
    {
        $message = [
            'nama_role.required' => 'nama_role wajib diisi',
            'nama_role.min' => 'nama_role minimal 3 karakter',
            'nama_role.max' => 'nama_role maksimal 100 karakter',
        ];

        $this->validate([
            'nama_role' => ['required', 'min:3', 'max:100'],
        ], $message);

        $roles = Role::find($this->role_id);

        if (!$roles) {
            $this->dispatch('sweet-alert', icon: 'error', title: 'role tidak ditemukan');
            return;
        }

        if ($roles) {
            $roles->update([
                'nama_role' => strtolower($this->nama_role),
            ]);


            $this->dispatch('hide-edit-modal');
            $this->reset();
            $this->dispatch('success');
            LivewireAlert::title('Berhasil Edit')
                ->text('Berhasil edit role')
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
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoleModal">
                        <strong>Edit role</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="roleUpdate">
                        <div class=" mb-3">
                            <label for="exampleInputNamarole1" class="form-label">Nama Role</label>
                            <input wire:model="nama_role" type="text" class="form-control" id="nama_role"
                                aria-describedby="NamaroleHelp">
                            @error('nama_role')
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
            document.getElementById('editRoleModal')
        );

        modal?.hide();
    });
</script>