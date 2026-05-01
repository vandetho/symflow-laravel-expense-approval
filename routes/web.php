<?php

use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\ExpenseCreate;
use App\Livewire\Pages\ExpenseShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Dashboard::class)->name('dashboard');
Route::get('/expenses/new', ExpenseCreate::class)->name('expenses.create');
Route::get('/expenses/{expense}', ExpenseShow::class)->name('expenses.show');
