<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');
Route::view('/barcode-generator', 'barcode-generator')->name('barcode-generator');
Route::view('/percentage-calculator', 'percentage-calculator')->name('percentage-calculator');
Route::view('/character-counter', 'character-counter')->name('character-counter');
