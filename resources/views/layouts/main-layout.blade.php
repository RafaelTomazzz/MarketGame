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
                    <button class="btn btn-primary rounded-circle mg-header-icon" type="button"
                        aria-label="Conta de {{ auth()->user()->nome }}" title="Conta do usuário">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                    </button>
                @else
                    <a class="btn btn-primary rounded-circle mg-header-icon" href="{{ route('login') }}"
                        aria-label="Entrar na conta" title="Entrar">
                        <i class="bi bi-person-fill" aria-hidden="true"></i>
                    </a>
                    <a class="btn btn-primary btn-sm d-none d-md-inline-flex" href="{{ route('cadastro') }}">Criar conta</a>
                @endauth

                <button class="btn btn-primary rounded-circle mg-header-icon" type="button"
                    aria-label="Carrinho indisponível no momento" title="Carrinho em breve">
                    <i class="bi bi-cart-fill" aria-hidden="true"></i>
                </button>
            </div>
        </nav>
    </header>

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
