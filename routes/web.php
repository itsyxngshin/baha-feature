<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\BahaMap; // Import your component

// 1. The Main "Baha Map" Route
// This tells Laravel: "When the user visits /map, render the BahaMap component"
Route::get('/map', BahaMap::class)->name('baha.map');

// 2. Placeholder Routes for the other menu items (optional)
// You can replace these with actual components later
Route::get('/', function () {
    return redirect()->route('baha.map'); // Redirect home to map for now
})->name('home');

Route::get('/services', function () {
    return view('services'); // You'll need to create a resources/views/services.blade.php
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
