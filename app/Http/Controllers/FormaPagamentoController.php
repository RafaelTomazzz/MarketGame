<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormaPagamentoRequest;
use App\Services\FormaPagamentoService;
use Illuminate\Http\RedirectResponse;

class FormaPagamentoController extends Controller
{
    public function __construct(private FormaPagamentoService $formaPagamentoService) {}

    public function store(StoreFormaPagamentoRequest $request): RedirectResponse
    {
        $formaPagamento = $this->formaPagamentoService->store(
            $request->user(),
            $request->validated(),
        );

        return redirect()
            ->route('cart.index')
            ->with([
                'success' => 'Método de pagamento cadastrado com sucesso.',
                'selected_payment_method_id' => $formaPagamento->id,
            ]);
    }
}
