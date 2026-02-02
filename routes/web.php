<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BahaMap; 
use App\Livewire\Admin\AddHotspot;

Route::get('/admin/add-hotspot', AddHotspot::class)->name('admin.add');
Route::get('/map', BahaMap::class)->name('baha.map');
Route::get('/', function () {
    return redirect()->route('baha.map'); // Redirect home to map for now
})->name('home');

Route::get('/services', function () {
    return view('services'); 
})->name('services');

Route::get('/profile', function () {
    return "Profile Page Coming Soon"; 
})->name('profile');

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
