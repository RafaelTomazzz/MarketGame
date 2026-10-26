<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.main-layout', function (ViewInstance $view): void {
            $user = auth()->user();
            $pedidos = $user?->pedidos()
                ->with([
                    'jogos',
                    'pagamentos.formaPagamento',
                ])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get() ?? collect();

            $view->with('cartItemsCount', $user?->jogosNoCarrinho()->count() ?? 0);
            $view->with('pedidosDoUsuario', $pedidos);
        });
    }
}
