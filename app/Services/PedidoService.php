<?php

namespace App\Services;

use App\Models\FormaPagamento;
use App\Models\Jogo;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PedidoService
{
    public function __construct(private CartService $cartService) {}

    public function criarPedido(User $usuario, int $formaPagamentoId): Pedido
    {
        return DB::transaction(function () use ($usuario, $formaPagamentoId): Pedido {
            $itensCarrinho = DB::table('jogo_user')
                ->where('user_id', $usuario->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);

            if ($itensCarrinho->isEmpty()) {
                throw ValidationException::withMessages([
                    'carrinho' => 'Adicione ao menos um jogo antes de finalizar o pedido.',
                ]);
            }

            $formaPagamento = FormaPagamento::query()
                ->where('user_id', $usuario->id)
                ->whereKey($formaPagamentoId)
                ->first();

            if ($formaPagamento === null) {
                throw ValidationException::withMessages([
                    'forma_pagamento_id' => 'Selecione um método de pagamento válido.',
                ]);
            }

            $carrinho = $this->cartService->obterCarrinho($usuario);
            $carrinhoItens = $carrinho['carrinhoItens'];

            if ($carrinhoItens->count() !== $itensCarrinho->count()) {
                throw ValidationException::withMessages([
                    'carrinho' => 'Um ou mais jogos não estão mais disponíveis. Revise seu carrinho antes de finalizar.',
                ]);
            }

            $itensPorJogo = $carrinhoItens->groupBy('id');
            $subtotalCentavos = $this->cartService->calcularSubtotalEmCentavos($carrinhoItens);
            $freteCentavos = CartService::FRETE_CENTAVOS;
            $totalCentavos = $subtotalCentavos + $freteCentavos;
            $total = $this->cartService->formatarCentavos($totalCentavos);
            $frete = $this->cartService->formatarCentavos($freteCentavos);

            $pedido = $usuario->pedidos()->create([
                'status' => 'pendente',
                'total' => $total,
                'frete' => $frete,
            ]);

            $pedido->jogos()->attach($this->prepararItensDoPedido($itensPorJogo));
            $pedido->pagamentos()->create([
                'forma_pagamento_id' => $formaPagamento->id,
                'valor' => $total,
            ]);

            DB::table('jogo_user')
                ->whereIn('id', $itensCarrinho->pluck('id'))
                ->where('user_id', $usuario->id)
                ->delete();

            return $pedido;
        });
    }

    /**
     * @param  Collection<int|string, Collection<int, Jogo>>  $itensPorJogo
     * @return array<int, array{quantidade: int, preco_original: string, preco_unitario: string}>
     */
    private function prepararItensDoPedido(Collection $itensPorJogo): array
    {
        $itens = [];

        foreach ($itensPorJogo as $jogos) {
            $jogo = $jogos->first();
            $precoOriginal = (string) $jogo->preco;
            $precoUnitario = (string) ($jogo->promocao?->valor ?? $jogo->preco);

            $itens[$jogo->id] = [
                'quantidade' => $jogos->count(),
                'preco_original' => $precoOriginal,
                'preco_unitario' => $precoUnitario,
            ];
        }

        return $itens;
    }
}
