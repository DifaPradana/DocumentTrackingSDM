<?php

use App\Models\Role;
use App\Models\User;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{

    public $nama_karyawan;
    public $password;
    public $role;
    public $user_id;

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

    #[On('akun-created')]
    public function refreshData()
    {
        $this->resetPage();
    }

    #[On('success')]
    public function render()
    {
        return $this->view([
            'users' => User::select(
                'user_id',
                'nama_karyawan',
                'role_id',
                'created_at'
            )
                ->search($this->search)
                ->orderBy('user_id', 'asc')
                ->paginate($this->perPage)
        ])
            ->layout('layouts.main')
            ->title('DocTracker | Akun');
    }

    public function delete(User $user)
    {
        $user->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete akun')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function editAkun($user_id)
    {
        $this->dispatch('open-edit-akun', user_id: $user_id);
        // dd("Kirim dispatch + $user_id");
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manajemen Akun</h5>
            <a href="#tambahAkunModal" data-bs-toggle="modal" class="btn btn-primary m-1">
                Tambah Akun
            </a>
        </div>
        <livewire:admin.account.create-akun />
        <livewire:admin.account.edit-akun />

        <div>
            {{-- <section class="mt-10"> --}}
            <div class="mx-auto w-full px-4">
                <!-- Start coding here -->
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between d p-4">
                        <div class="flex">
                            <div class="relative w-full">
                                <input wire:model.live.debounce.300ms="search" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 "
                                    placeholder="Search" required="">
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border border-gray-300 text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="border border px-4 py-3 text-center">Nama</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Role</th>
                                    <th scope="col" class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if ($users->isEmpty())
                                <tr>
                                    <td colspan="6" class="border px-4 py-3 text-center text-black">Tidak ditemukan</td>
                                </tr>
                                @else
                                @foreach ($users as $user)
                                <tr wire:key="{{ $user->user_id }}" class="border-b dark:border-gray-300">
                                    <td
                                        class="border px-4 py-3 text-center text-black {{ auth()->user()->nama_karyawan === $user->nama_karyawan ? 'text-red-500' : '' }}">
                                        {{ ucwords($user->nama_karyawan) }}
                                        {{ auth()->user()->nama_karyawan === $user->nama_karyawan ? '(Anda)' : '' }}
                                    </td>
                                    <td class="border px-4 py-3 text-black text-center">{{ ucwords($user->role->nama_role) }}
                                    </td>
                                    <!-- <td class="border px-4 py-3 text-center text-black">
                                        {{ $user->created_at->format('d/m/Y - H:i:s') }}
                                    </td> -->
                                    <td class="px-4 py-3  text-center text-black">
                                        <button
                                            type="button"
                                            wire:click="editAkun({{ $user->user_id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning m-1">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        @if ($user->role->nama_role != 'admin')
                                        <button
                                            onclick="confirm('Kamu akan menghapus akun {{ $user->nama_karyawan }} secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $user->user_id }})"
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
                        {{ $users->links() }}
                    </div>
                </div>
                {{-- </section> --}}
            </div>
        </div>
        <br>
    </div>
    <script>
        function registerListeners() {
            Livewire.on('show-edit-modal', () => {
                const modalEl = document.getElementById('editAkunModal');
                if (!modalEl) return;
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                if (!modalEl.classList.contains('show')) modal.show();
            });

            Livewire.on('hide-edit-modal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editAkunModal'));
                modal?.hide();
            });
        }

        document.addEventListener('livewire:init', registerListeners);
        document.addEventListener('livewire:navigated', registerListeners);
    </script>
</div>