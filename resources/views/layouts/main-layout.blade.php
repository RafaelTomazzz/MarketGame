<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'MarketGame')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>

<body class="d-flex min-vh-100 flex-column">
    <header class="border-bottom border-secondary bg-dark shadow-sm">
        <nav class="container-fluid d-flex justify-content-between align-items-center p-3"
            aria-label="Navegação principal">
            <a class="d-inline-flex align-items-center gap-2 text-decoration-none text-primary fs-4 fw-semibold"
                href="{{ route('home') }}">
                <img src="{{ asset('img/estrela_logo.svg') }}" alt="" width="28" height="34"
                    aria-hidden="true">
                <span class="fw-light" style="font-size: 1.25rem">MarketGame</span>
            </a>

            <div class="order-3 order-lg-2 flex-grow-1 d-flex justify-content-center px-3">
                <form class="input-group rounded-pill mg-search-form" action="{{ route('home') }}"
                    method="GET" role="search">
                    <label class="visually-hidden" for="site-search">Buscar jogos</label>
                    <input id="site-search" name="q" type="search" class="form-control rounded-start-pill"
                        placeholder="Buscar jogos..." value="{{ request('q') }}">
                    <button class="btn btn-primary rounded-end-pill px-3" type="submit">Buscar</button>
                </form>
            </div>

            <div class="order-2 order-lg-3 d-flex align-items-center justify-content-end gap-2">
                @auth
                    <span class="fw-semibold d-none d-sm-inline">{{ auth()->user()->nome }}</span>
                    <div class="dropdown">
                        <button id="user-menu-button" class="btn btn-primary rounded-circle mg-header-icon"
                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                            aria-label="Conta de {{ auth()->user()->nome }}" title="Conta do usuário">
                            <i class="bi bi-person-fill" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="user-menu-button">
                            <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                data-bs-target="#user-orders-modal">
                                Meus pedidos
                            </button>
                            <button class="dropdown-item" type="button">Logout</button>
                        </div>
                    </div>
                @else
                    <a class="btn btn-primary rounded-circle mg-header-icon" href="{{ route('login') }}"
                        aria-label="Entrar na conta" title="Entrar">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-primary btn-sm d-none d-md-inline-flex" href="{{ route('cadastro') }}">Criar conta</a>
                @endauth

                <a class="btn btn-primary rounded-circle mg-header-icon position-relative"
                    href="{{ route('cart.index') }}" aria-label="Carrinho: {{ $cartItemsCount }} itens"
                    title="Carrinho: {{ $cartItemsCount }} itens">
                    <i class="bi bi-cart-fill" aria-hidden="true"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                        {{ $cartItemsCount }}
                        <span class="visually-hidden">itens no carrinho</span>
                    </span>
                </a>
            </div>
        </nav>
    </header>

    @auth
        <div class="modal fade" id="user-orders-modal" tabindex="-1" aria-labelledby="user-orders-modal-title"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content border-secondary bg-dark mg-orders-modal-content">
                    <div class="modal-header border-secondary">
                        <div>
                            <p class="text-primary text-uppercase fw-semibold small mb-1">Sua conta</p>
                            <h2 class="modal-title fs-5 fw-bold" id="user-orders-modal-title">Meus pedidos</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        @forelse ($pedidosDoUsuario as $pedido)
                            <article class="card border-secondary bg-body-tertiary mb-3">
                                <div class="card-body">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                        <div>
                                            <h3 class="h6 fw-bold mb-1">Pedido #{{ $pedido->id }}</h3>
                                            <p class="small text-body-secondary mb-0">
                                                Realizado em {{ $pedido->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                        <span class="badge text-bg-warning">
                                            {{ match ($pedido->status) {
                                                'pendente' => 'Pendente',
                                                'aprovado' => 'Aprovado',
                                                'cancelado' => 'Cancelado',
                                                default => ucfirst($pedido->status),
                                            } }}
                                        </span>
                                    </div>

                                    <h4 class="small fw-semibold">Itens do pedido</h4>
                                    <ul class="list-group list-group-flush mb-3">
                                        @foreach ($pedido->jogos as $jogo)
                                            <li class="list-group-item bg-transparent px-0 d-flex justify-content-between gap-3">
                                                <span>
                                                    {{ $jogo->nome }}
                                                    <span class="text-body-secondary">× {{ $jogo->pivot->quantidade }}</span>
                                                </span>
                                                @if ($jogo->pivot->preco_unitario !== null)
                                                    <span class="text-nowrap">
                                                        R$ {{ number_format((float) $jogo->pivot->preco_unitario, 2, ',', '.') }}
                                                        <span class="small text-body-secondary">cada</span>
                                                    </span>
                                                @else
                                                    <span class="small text-body-secondary">Preço não registrado</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="d-flex flex-wrap justify-content-between gap-3">
                                        <div>
                                            <p class="small text-body-secondary mb-1">Pagamento</p>
                                            @forelse ($pedido->pagamentos as $pagamento)
                                                <p class="mb-1">
                                                    {{ $pagamento->formaPagamento?->nome ?? 'Método indisponível' }}
                                                    <span class="text-body-secondary">
                                                        · R$ {{ number_format((float) $pagamento->valor, 2, ',', '.') }}
                                                    </span>
                                                </p>
                                            @empty
                                                <p class="mb-0">Nenhum pagamento registrado</p>
                                            @endforelse
                                        </div>
                                        <div class="text-end">
                                            <p class="small text-body-secondary mb-1">
                                                Frete
                                                @if ($pedido->frete !== null)
                                                    R$ {{ number_format((float) $pedido->frete, 2, ',', '.') }}
                                                @else
                                                    não registrado
                                                @endif
                                            </p>
                                            <p class="fw-bold text-primary mb-0">
                                                Total: R$ {{ number_format((float) $pedido->total, 2, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="py-5 text-center">
                                <i class="bi bi-receipt display-5 text-body-secondary" aria-hidden="true"></i>
                                <p class="h5 mt-3 mb-2">Você ainda não fez pedidos</p>
                                <p class="text-body-secondary mb-0">
                                    Seus pedidos aparecerão aqui depois que você finalizar uma compra.
                                </p>
                            </div>
                        @endforelse
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    @endauth

    <main class="flex-grow-1">
        @yield('content')
    </main>

    <footer class="mt-auto border-top border-secondary py-4">
        <div
            class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-body-secondary small">
            <span>&copy; {{ now()->year }} MarketGame</span>
            <span>Jogos físicos para quem ama jogar.</span>
        </div>
    </footer>
</body>

</html>
