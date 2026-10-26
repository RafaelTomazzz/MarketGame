@extends('layouts.main-layout')

@section('title', 'Meu carrinho | MarketGame')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="mb-4">
            <p class="text-primary text-uppercase fw-semibold small mb-1">Revise sua seleção</p>
            <h1 class="h2 fw-bold mb-0">Meu carrinho</h1>
        </div>

        <div class="row g-4 align-items-start">
            <section class="col-lg-8" aria-labelledby="cart-items-heading">
                <h2 id="cart-items-heading" class="visually-hidden">Itens do carrinho</h2>

                @forelse ($carrinhoItens as $jogo)
                    @php
                        $precoUnitario = $jogo->promocao
                            ? (float) $jogo->promocao->valor
                            : (float) $jogo->preco;
                    @endphp

                    <article class="card border-secondary bg-dark shadow-sm mb-3">
                        <div class="card-body row g-3 align-items-center">
                            <div class="col-3 col-sm-2">
                                <div class="ratio ratio-4x3 rounded-3 bg-secondary overflow-hidden">
                                    @if ($jogo->imagem)
                                        <img src="{{ $jogo->imagem }}" alt="Capa de {{ $jogo->nome }}"
                                            class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center text-body-secondary">
                                            <i class="bi bi-controller fs-2" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col">
                                <h2 class="h5 fw-semibold mb-1">{{ $jogo->nome }}</h2>
                                @if ($jogo->categorias->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach ($jogo->categorias->take(2) as $categoria)
                                            <span class="badge rounded-pill text-bg-secondary">{{ $categoria->nome }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="col-auto">
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <strong class="text-primary text-nowrap">
                                        R$ {{ number_format($precoUnitario, 2, ',', '.') }}
                                    </strong>
                                    <form action="{{ route('cart.remove', $jogo->pivot->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1" aria-hidden="true"></i>
                                            Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-4 border border-secondary bg-dark p-5 text-center">
                        <i class="bi bi-cart3 display-5 text-body-secondary" aria-hidden="true"></i>
                        <p class="h5 mt-3 mb-2">Seu carrinho está vazio</p>
                        <p class="text-body-secondary mb-3">Explore os jogos e adicione seus favoritos ao carrinho.</p>
                        <a class="btn btn-primary" href="{{ route('home') }}">Explorar jogos</a>
                    </div>
                @endforelse

                <section class="card border-secondary bg-dark shadow-sm mt-4"
                    aria-labelledby="payment-methods-heading">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <p class="text-primary text-uppercase fw-semibold small mb-1">Pagamento</p>
                                <h2 id="payment-methods-heading" class="h4 fw-bold mb-0">Método de pagamento</h2>
                            </div>
                            <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse"
                                data-bs-target="#add-payment-method" aria-expanded="false"
                                aria-controls="add-payment-method">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
                                Adicionar método
                            </button>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success" role="status">{{ session('success') }}</div>
                        @endif
                        @if (session('order_confirmation'))
                            <div class="alert alert-success" role="status">
                                Pedido #{{ session('order_confirmation.id') }} criado com sucesso e aguardando pagamento.
                                Total: R$ {{ number_format((float) session('order_confirmation.total'), 2, ',', '.') }}.
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($formasPagamento->isNotEmpty())
                            <fieldset>
                                <legend class="visually-hidden">Selecione o método de pagamento</legend>
                                <div class="list-group">
                                    @foreach ($formasPagamento as $formaPagamento)
                                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                            <input class="form-check-input flex-shrink-0" type="radio"
                                                name="forma_pagamento_id" value="{{ $formaPagamento->id }}"
                                                form="checkout-form" required
                                                @checked((string) (old('forma_pagamento_id') ?? session('selected_payment_method_id')) === (string) $formaPagamento->id)>
                                            <span class="flex-grow-1">{{ $formaPagamento->nome }}</span>
                                            <i class="bi bi-credit-card text-primary" aria-hidden="true"></i>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @else
                            <p class="text-body-secondary mb-0">
                                Você ainda não cadastrou métodos de pagamento. Adicione um para continuar.
                            </p>
                        @endif

                        <div class="collapse mt-3 @if ($errors->hasAny(['tipo', 'numero_cartao', 'titular_cartao', 'bandeira', 'validade', 'cvv'])) show @endif"
                            id="add-payment-method">
                            <form action="{{ route('payment-methods.store') }}" method="POST"
                                class="rounded-3 border border-secondary p-3">
                                @csrf
                                <label for="payment-type" class="form-label">Tipo de pagamento</label>
                                <select id="payment-type" name="tipo"
                                    class="form-select @error('tipo') is-invalid @enderror" required>
                                    <option value="">Selecione uma opção</option>
                                    <option value="debito" @selected(old('tipo') === 'debito')>Cartão de débito</option>
                                    <option value="credito" @selected(old('tipo') === 'credito')>Cartão de crédito</option>
                                    <option value="pix" @selected(old('tipo') === 'pix')>Pix</option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div id="card-details" class="row g-3 mt-1"
                                    @if (! in_array(old('tipo'), ['debito', 'credito'], true)) hidden @endif>
                                    <div class="col-12">
                                        <label for="cardholder-name" class="form-label">Nome impresso no cartão</label>
                                        <input id="cardholder-name" name="titular_cartao" type="text"
                                            autocomplete="cc-name"
                                            class="form-control @error('titular_cartao') is-invalid @enderror"
                                            value="{{ old('titular_cartao') }}" maxlength="255">
                                        @error('titular_cartao')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="card-number" class="form-label">Número do cartão</label>
                                        <input id="card-number" name="numero_cartao" type="text"
                                            inputmode="numeric" autocomplete="cc-number" maxlength="23"
                                            class="form-control @error('numero_cartao') is-invalid @enderror"
                                            aria-describedby="card-number-help">
                                        @error('numero_cartao')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div id="card-number-help" class="form-text">
                                            Usamos o número apenas para identificar os 4 dígitos finais; ele não será salvo.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="card-brand" class="form-label">Bandeira</label>
                                        <select id="card-brand" name="bandeira"
                                            class="form-select @error('bandeira') is-invalid @enderror">
                                            <option value="">Selecione</option>
                                            @foreach (['Visa', 'Mastercard', 'Elo', 'American Express', 'Hipercard', 'Outra'] as $bandeira)
                                                <option value="{{ $bandeira }}"
                                                    @selected(old('bandeira') === $bandeira)>{{ $bandeira }}</option>
                                            @endforeach
                                        </select>
                                        @error('bandeira')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="card-expiration" class="form-label">Validade</label>
                                        <input id="card-expiration" name="validade" type="month"
                                            autocomplete="cc-exp"
                                            class="form-control @error('validade') is-invalid @enderror"
                                            value="{{ old('validade') }}">
                                        @error('validade')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <p class="form-text mt-3 mb-3">
                                    O número completo e o CVV não são armazenados. Este formulário não processa pagamentos.
                                </p>
                                <button type="submit" class="btn btn-primary">
                                    Salvar método
                                </button>
                            </form>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const paymentType = document.getElementById('payment-type');
                                const cardDetails = document.getElementById('card-details');
                                const cardInputs = cardDetails.querySelectorAll('input, select');

                                function updateCardFields() {
                                    const cardSelected = ['debito', 'credito'].includes(paymentType.value);

                                    cardDetails.hidden = !cardSelected;
                                    cardInputs.forEach(function (input) {
                                        input.required = cardSelected;
                                    });
                                }

                                paymentType.addEventListener('change', updateCardFields);
                                updateCardFields();
                            });
                        </script>
                    </div>
                </section>
            </section>

            <aside class="col-lg-4" aria-labelledby="cart-summary-heading">
                <div class="card border-secondary bg-dark shadow-sm sticky-top">
                    <div class="card-body p-4">
                        <h2 id="cart-summary-heading" class="h4 fw-bold mb-4">Resumo do pedido</h2>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-body-secondary">Subtotal ({{ $carrinhoItens->count() }} itens)</span>
                            <strong>
                                R$ {{ number_format((float) $subtotal, 2, ',', '.') }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-body-secondary">Frete</span>
                            <strong>R$ {{ number_format((float) $frete, 2, ',', '.') }}</strong>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-semibold">Total do pedido</span>
                            <strong class="fs-4 text-primary">
                                R$ {{ number_format((float) $total, 2, ',', '.') }}
                            </strong>
                        </div>

                        <hr class="border-secondary">

                        <form id="checkout-form" action="{{ route('orders.store') }}" method="POST">
                            @csrf
                            <button class="btn btn-primary w-100" type="submit"
                                @disabled($carrinhoItens->isEmpty() || $formasPagamento->isEmpty())>
                                Finalizar pedido
                            </button>
                        </form>
                        <p class="small text-body-secondary text-center mt-3 mb-0">
                            Seu pedido ficará pendente até a confirmação do pagamento.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
