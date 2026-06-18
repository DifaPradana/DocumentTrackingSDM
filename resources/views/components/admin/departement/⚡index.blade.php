<?php

use App\Models\Departement;
use App\Models\User;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{

    public $nama_departement;

    use WithPagination;
    public $perPage = 20;
    public $search = '';

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('departement-created')]
    public function refreshData()
    {
        $this->resetPage();
    }

    #[On('success')]
    public function render()
    {
        return $this->view([
            'departements' => Departement::select(
                'departement_id',
                'nama_departement',
            )
                ->search($this->search)
                ->orderBy('departement_id', 'asc')
                ->paginate($this->perPage)
        ])
            ->layout('layouts.main')
            ->title('DocTracker | Akun');
    }

    public function delete(Departement $departement)
    {
        $departement->delete();
        LivewireAlert::title('Berhasil')
            ->text('Kamu berhasil delete departement')
            ->success()
            ->toast()
            ->position('top-end')
            ->timer(3000)
            ->show();
    }

    public function editDepartement($departement_id)
    {
        $this->dispatch('open-edit-departement', departement_id: $departement_id);
        // dd("Kirim dispatch + $departement_id");
    }
};
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title fw-semibold mb-4">Manajemen Departement</h5>
            <a href="#tambahDepartementModal" data-bs-toggle="modal" class="btn btn-primary m-1">
                Tambah Departement
            </a>
        </div>
        <livewire:admin.departement.create-departement />
        <livewire:admin.departement.edit-departement />

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
                                    <th scope="col" class="border px-4 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if ($departements->isEmpty())
                                <tr>
                                    <td colspan="6" class="border px-4 py-3 text-center text-black">Tidak ditemukan</td>
                                </tr>
                                @else
                                @foreach ($departements as $departement)
                                <tr wire:key="{{ $departement->departement_id }}" class="border-b dark:border-gray-300">
                                    <td class="border px-4 py-3 text-black text-center">{{ ucwords($departement->nama_departement) }}
                                    </td>
                                    <td class="px-4 py-3  text-center text-black">
                                        <button
                                            type="button"
                                            wire:click="editDepartement({{ $departement->departement_id }})"
                                            wire:loading.attr="disabled"
                                            class="btn btn-warning m-1">
                                            <i class="ti ti-pencil"></i>
                                        </button>
                                        <button
                                            onclick="confirm('Kamu akan menghapus depaterment {{ $departement->nama_departement }} secara permanen, apakah yakin?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $departement->departement_id }})"
                                            class="btn btn-danger m-1">
                                            <i class="ti ti-trash" aria-hidden="true"></i>
                                        </button>
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
                        {{ $departements->links() }}
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
                const modalEl = document.getElementById('editDepartementModal');
                if (!modalEl) return;
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                if (!modalEl.classList.contains('show')) modal.show();
            });

            Livewire.on('hide-edit-modal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editDepartementModal'));
                modal?.hide();
            });
        }

        document.addEventListener('livewire:init', registerListeners);
        document.addEventListener('livewire:navigated', registerListeners);
    </script>
</div>