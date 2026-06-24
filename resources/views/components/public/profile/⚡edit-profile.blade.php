<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public $nama_karyawan;
    public $password;

    public function save()
    {
        /** @var User $user */
        $user = Auth::user();
        $message = [
            'password.required' => 'Password wajib diisi jika mau diganti',
            'password.min'      => 'Password minimal 8 digit',
            'password.max'      => 'Password maksimal 20 digit',
        ];

        $this->validate([
            'password' => ['required', 'min:8', 'max:20']
        ], $message);

        $user->update([
            'password' => $this->password ? $this->password : $user->password,
        ]);

        LivewireAlert::title('Berhasil Edit Profile')
            ->success()
            ->timer(3000)
            ->toast()
            ->position('top-end')
            ->show();

        $this->dispatch('profile-updated');
    }

    public function mount()
    {
        $user = Auth::user();

        $this->nama_karyawan = ucwords(strtolower($user->nama_karyawan));
    }
};
?>

<div>
    <div class="container-fluid">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-semibold mb-4">Profile</h5>
                    <div class="card">
                        <div class="card-body">
                            <form wire:submit="save">
                                <fieldset disabled>
                                    <div class="mb-3">
                                        <label for="exampleInputNama1" class="form-label">Nama Karyawan</label>
                                        <input wire:model="nama_karyawan" type="text" class="form-control" id="exampleInputNama1" aria-describedby="NamaHelp">
                                        <div id="NamaHelp" class="form-text">Hubungi admin untuk mengubah nama</div>
                                    </div>
                                </fieldset>
                                <div class="mb-3">
                                    <label class="form-label">Password</label>

                                    <div style="position:relative; display:flex; align-items:center;">

                                        <input
                                            type="password" wire:model="password"
                                            class="form-control"
                                            id="password"
                                            style="padding-right:40px;">
                                        <span
                                            onclick="togglePassword()"
                                            style="position:absolute; right:12px; cursor:pointer; color:#888; display:flex; align-items:center; height:100%;">
                                            <i class="ti ti-eye" id="iconPassword"></i>
                                        </span>
                                    </div>
                                    @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    <div id="PasswordHelp" class="form-text">Isi untuk mengganti password</div>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            const icon = document.getElementById("iconPassword");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("ti-eye");
                icon.classList.add("ti-eye-off");
            } else {
                input.type = "password";
                icon.classList.remove("ti-eye-off");
                icon.classList.add("ti-eye");
            }
        }
    </script>
</div>