<?php

use App\Models\Departement;
use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component
{
    public $departement_id;
    public $nama_departement;

    public function closeModal()
    {
        $this->reset();
        $this->resetValidation();
    }

    #[On('open-edit-departement')]
    public function editDepartement($departement_id)
    {
        $departement = Departement::find($departement_id);

        $this->departement_id = $departement->departement_id;
        $this->nama_departement = $departement->nama_departement;

        $this->dispatch('show-edit-modal');
    }

    public function save()
    {
        LivewireAlert::title('Changes saved!')
            ->success()
            ->show();
    }


    public function departementUpdate()
    {
        $message = [
            'nama_departement.required' => 'nama_departement wajib diisi',
            'nama_departement.max' => 'nama_departement maksimal 100 karakter',
        ];

        $this->validate([
            'nama_departement' => ['required', 'string', 'max:100'],
        ], $message);

        $departement = Departement::find($this->departement_id);
        // dd($this->status);
        if (!$departement) {
            $this->dispatch('sweet-alert', icon: 'error', title: 'Departement tidak ditemukan');
            return;
        }

        if ($departement) {
            $departement->update([
                'nama_departement' => $this->nama_departement,
            ]);


            $this->dispatch('hide-edit-modal');
            $this->reset();
            $this->dispatch('success');
            LivewireAlert::title('Berhasil Edit')
                ->text('Berhasil edit data departement')
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
    <div class="modal fade" id="editDepartementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDepartementModal">
                        <strong>Edit Departement</strong>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" wire:click="closeModal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="departementUpdate">
                        <div class=" mb-3">
                            <label for="exampleInputNamaKaryawan1" class="form-label">Nama Karyawan</label>
                            <input wire:model="nama_departement" type="text" class="form-control" id="nama_departement"
                                aria-describedby="NamaKaryawanHelp">
                            @error('nama_departement')
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
            document.getElementById('editDepartementModal')
        );

        modal?.hide();
    });
</script>