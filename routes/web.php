<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'auth.login')->name('login');
Route::get('/login', function () {
    return redirect('/');
})->middleware('guest');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

Route::group(['middleware' => ['auth', 'role:Admin'], 'prefix' => 'admin'], function () {
    Route::livewire('/dashboard', 'admin.dashboard.index')->name('dashboard.page');
    Route::livewire('/account', 'admin.account.index')->name('account.page');
    Route::livewire('/role', 'admin.role.index')->name('role.page');
    Route::livewire('/departement', 'admin.departement.index')->name('departement.page');
});

Route::group(['middleware' => ['auth', 'role:Office'], 'prefix' => 'office'], function () {
    Route::livewire('/dashboard', 'office.dashboard.index')->name('office.dashboard.page');
    Route::livewire('/document', 'office.pengajuan.index')->name('office.pengajuan.page');
    Route::livewire('/document-status', 'office.pengajuan-detail.index')->name('office.pengajuan-detail.page');
});

Route::group(['middleware' => ['auth', 'role:ADM Kilang'], 'prefix' => 'adm-kilang'], function () {
    Route::livewire('/dashboard', 'kilang.dashboard.index')->name('adm.dashboard.page');
    Route::livewire('/task', 'kilang.task.index')->name('adm.task.page');
});

require __DIR__ . '/settings.php';
