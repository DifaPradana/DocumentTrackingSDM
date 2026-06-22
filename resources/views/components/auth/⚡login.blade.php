<?php

use Illuminate\Support\Facades\Auth;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Attributes\Layout;

new class extends Component {
    public function render()
    {
        return $this->view()->layout('layouts.auth')->title('Login');
    }

    public $nama_karyawan = '';
    public $password = '';

    public function login()
    {
        $this->validate([
            'nama_karyawan' => 'required',
            'password' => 'required',
        ]);

        if (!Auth::attempt([
            'nama_karyawan' => strtolower($this->nama_karyawan),
            'password' => $this->password,
            // 'is_active' => true,
        ])) {

            $this->addError(
                'nama_karyawan',
                'Nama atau password salah'
            );

            return;
        }

        $user = Auth::user();

        request()->session()->regenerate();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Berhasil Login'
        ]);

        $user = Auth::user();

        switch ($user->role_id) {
            case '1':
                $route = 'dashboard.page';
                break;

            case '2':
                $route = 'office.dashboard.page';
                break;

            default:
                $route = 'adm.dashboard.page';
                break;
        }

        return $this->redirectRoute($route);
    }
};
?>

<div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
    <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
            <div class="col-md-8 col-lg-6 col-xxl-3">
                <div class="card mb-0">
                    <div class="card-body">
                        <a href="/" class="text-nowrap logo-img text-center d-block py-3 w-100">
                            <img src="{{ asset('assets/images/logos/sdmlogowithtext.png') }}" width="300" alt="">
                        </a>
                        <div class="text-center mb-4">
                            <h5 class="fw-bold">Sistem Tracking Document</h5>
                        </div>

                        <form wire:submit="login">
                            <div class="mb-3">
                                <label for="nama_karyawan" class="form-label">Nama</label>
                                <input wire:model="nama_karyawan" type="text"
                                    placeholder="Nama"
                                    class="form-control @error('nama_karyawan') is-invalid @enderror"
                                    id="nama_karyawan" required />
                                @error('nama_karyawan')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input wire:model="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password" placeholder="Password" />
                                @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit"
                                class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>Login</span>
                                <span wire:loading>Loading...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>