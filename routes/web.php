<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/cadastro', [AuthController::class, 'cadastro'])->name('cadastro');
Route::post('/cadastro', [AuthController::class, 'cadastrar'])->name('cadastro.store');
