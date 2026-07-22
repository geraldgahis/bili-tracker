<?php

use Illuminate\Support\Facades\Route;


Route::livewire('/', 'pages::auth.login')->name('login');
Route::livewire('/register', 'pages::auth.register')->name('register');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::livewire('/categories', 'pages::categories.index')->name('categories');
    Route::livewire('/products', 'pages::products.index')->name('products.index');
    Route::livewire('/products/create', 'pages::products.create')->name('products.create');
});