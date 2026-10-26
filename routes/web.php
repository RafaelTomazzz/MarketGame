<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/cart', [CartController::class, 'index'])
    ->middleware('auth')
    ->name('cart.index');

Route::post('/cart/{jogo}', [CartController::class, 'add'])
    ->middleware('auth')
    ->name('cart.add');

Route::delete('/cart/{item}', [CartController::class, 'destroy'])
    ->whereNumber('item')
    ->middleware('auth')
    ->name('cart.remove');

Route::post('/orders', [PedidoController::class, 'store'])
    ->middleware('auth')
    ->name('orders.store');

Route::post('/payment-methods', [FormaPagamentoController::class, 'store'])
    ->middleware('auth')
    ->name('payment-methods.store');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
Route::get('/cadastro', [AuthController::class, 'cadastro'])->name('cadastro');
Route::post('/cadastro', [AuthController::class, 'cadastrar'])->name('cadastro.store');
