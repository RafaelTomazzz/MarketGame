<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(Request $request): View
    {
        $carrinho = $this->cartService->obterCarrinho($request->user());
        $carrinho['formasPagamento'] = $request->user()
            ->formasPagamento()
            ->orderBy('nome')
            ->orderBy('id')
            ->get();

        return view('cart.index', $carrinho);
    }

    public function add(Request $request, Jogo $jogo): RedirectResponse
    {
        $this->cartService->adicionarJogo($request->user(), $jogo);

        return back()->with([
            'success' => 'Jogo adicionado ao carrinho com sucesso.',
            'cart_added_game_id' => $jogo->id,
        ]);
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        abort_unless($this->cartService->removerUnidade($request->user(), $item), 404);

        return back()->with('success', 'Item removido do carrinho com sucesso.');
    }
}
