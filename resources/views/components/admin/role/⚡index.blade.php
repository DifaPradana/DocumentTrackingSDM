<?php

use App\Models\Role;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{

    public $nama_role;

    #[On('akun-created')]
    public function refreshData()
    {
        $this->resetPage();
    }

    #[On('success')]
    public function render()
    {
        return $this->view([
            'roles' => Role::select(
                'role_id',
                'nama_role'
            )
                ->orderBy('role_id', 'asc')
                ->paginate($this->perPage)
        ])
            ->layout('layouts.main')
            ->title('DocTracker | Role');
    }


    use WithPagination;
    public $perPage = 20;
    public $search = '';

    public function getRoles()
    {
        return Role::all();
    }


    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }


    public function delete(Role $role)
    {
        $role->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete role')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function editRole($role_id)
    {
        $this->dispatch('open-edit-role', role_id: $role_id);
        // dd("Kirim dispatch + $role_id");
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manajemen Role</h5>
            <a href="#tambahRoleModal" data-bs-toggle="modal" class="btn btn-primary m-1">
                Tambah Role
            </a>
        </div>

        <livewire:admin.role.create-role />
        <livewire:admin.role.edit-role />

        <div>
            {{-- <section class="mt-10"> --}}
            <div class="mx-auto w-full px-4">
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="border border px-4 py-3 text-center">Nama Role</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if ($roles->isEmpty())
                                <tr>
                                    <td colspan="6" class="border px-4 py-3 text-center text-black">Tidak ditemukan</td>
                                </tr>
                                @else
                                @foreach ($roles as $role)
                                <tr wire:key="{{ $role->role_id }}" class="border-b dark:border-gray-300">
                                    <td
                                        class="border px-4 py-3 text-center text-black">
                                        {{ ucwords($role->nama_role) }}
                                    </td>
                                    <td class="border px-4 py-3 text-center text-black">
                                        <button
                                            type="button"
                                            wire:click="editRole({{ $role->role_id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning m-1">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        @if ($role->nama_role != 'admin')
                                        <button
                                            onclick="confirm('Kamu akan menghapus akun {{ $role->nama_karyawan }} secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $role->role_id }})"
                                            class="btn btn-danger m-1">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="py-4 px-3">
                        <div class="flex ">
                            <div class="flex space-x-4 items-center mb-3">
                                <label class="w-32 text-sm font-medium text-gray-900">Per Page</label>
                                <select wire:model.live="perPage"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        {{ $roles->links() }}
                    </div>
                </div>
                {{-- </section> --}}
            </div>
        </div>
        <br>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-edit-modal', () => {
                const modalEl = document.getElementById('editRoleModal');

                let modal = bootstrap.Modal.getInstance(modalEl);

                if (!modal) {
                    modal = new bootstrap.Modal(modalEl);
                }

                if (!modalEl.classList.contains('show')) {
                    modal.show();
                }
            });
        });
    </script>
</div>