<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Services\PedidoService;
use Illuminate\Http\RedirectResponse;

class PedidoController extends Controller
{
    public function __construct(private PedidoService $pedidoService) {}

    public function store(StorePedidoRequest $request): RedirectResponse
    {
        $pedido = $this->pedidoService->criarPedido(
            $request->user(),
            (int) $request->validated('forma_pagamento_id'),
        );

        return redirect()
            ->route('cart.index')
            ->with('order_confirmation', [
                'id' => $pedido->id,
                'total' => $pedido->total,
            ]);
    }
}
