@extends('layouts.main-layout')

@section('title', 'Início | MarketGame')

@section('content')
    <div class="container py-4 py-lg-5">
        @php($jogosEmPromocaoNoCarrossel = $jogosEmPromocao->take(5))

        <section aria-labelledby="destaques-heading" class="mb-5">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <p class="text-primary text-uppercase fw-semibold small mb-1">Oportunidades por tempo limitado</p>
                    <h1 id="destaques-heading" class="h2 fw-bold mb-0">Jogos em promoção</h1>
                </div>
                <span class="text-body-secondary small">Ofertas selecionadas para você</span>
            </div>

            @if ($jogosEmPromocaoNoCarrossel->isNotEmpty())
                <div id="featured-games" class="carousel slide carousel-fade mg-featured-carousel"
                    data-bs-ride="carousel" data-bs-interval="6500">
                    <div class="carousel-indicators">
                        @foreach ($jogosEmPromocaoNoCarrossel as $jogo)
                            <button type="button" data-bs-target="#featured-games"
                                data-bs-slide-to="{{ $loop->index }}" @if ($loop->first) class="active"
                                aria-current="true" @endif
                                aria-label="Exibir promoção {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>

                    <div class="carousel-inner rounded-4 overflow-hidden shadow">
                        @foreach ($jogosEmPromocaoNoCarrossel as $jogo)
                            <article class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <div class="mg-featured-slide position-relative overflow-hidden">
                                    @if ($jogo->imagem)
                                        <img class="mg-featured-image position-absolute top-0 start-0 w-100 h-100"
                                            src="{{ $jogo->imagem }}" alt="" @if (!$loop->first) loading="lazy" @endif>
                                    @endif
                                    <div class="mg-featured-scrim position-absolute top-0 start-0 w-100 h-100"></div>

                                    <div class="mg-featured-content position-relative d-flex align-items-end h-100 p-4 p-lg-5">
                                        <div class="col-lg-7">
                                            <span class="badge text-bg-danger mb-3">Em promoção</span>
                                            <h2 class="display-5 fw-bold mb-2">{{ $jogo->nome }}</h2>
                                            <p class="text-white-50 mb-3">{{ $jogo->descricao }}</p>

                                            @if ($jogo->promocao)
                                                <div class="d-flex flex-wrap align-items-center gap-2">
                                                    @if ($jogo->promocao->percentual)
                                                        <span class="badge text-bg-danger">-{{ $jogo->promocao->percentual }}%</span>
                                                    @endif
                                                    <span class="text-decoration-line-through text-white-50">
                                                        R$ {{ number_format((float) $jogo->preco, 2, ',', '.') }}
                                                    </span>
                                                    <strong class="fs-4 text-primary">
                                                        R$ {{ number_format((float) $jogo->promocao->valor, 2, ',', '.') }}
                                                    </strong>
                                                </div>
                                            @else
                                                <strong class="fs-4 text-primary">
                                                    R$ {{ number_format((float) $jogo->preco, 2, ',', '.') }}
                                                </strong>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($jogosEmPromocaoNoCarrossel->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#featured-games"
                            data-bs-slide="prev" aria-label="Destaque anterior">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#featured-games"
                            data-bs-slide="next" aria-label="Próximo destaque">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="rounded-4 border border-secondary bg-dark p-5 text-center text-body-secondary">
                    Não há promoções ativas no momento. Volte em breve para conferir novas ofertas.
                </div>
            @endif
        </section>

        <section aria-labelledby="categories-heading" class="mb-5">
            <div class="mb-3">
                <p class="text-primary text-uppercase fw-semibold small mb-1">Encontre seu estilo</p>
                <h2 id="categories-heading" class="h3 fw-bold mb-0">Explore por categoria</h2>
            </div>

            @if ($categorias->isNotEmpty())
                <nav class="d-flex flex-wrap gap-2" aria-label="Categorias de jogos">
                    @foreach ($categorias as $categoria)
                        <a class="btn {{ $categoriaSelecionada?->is($categoria) ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill"
                            href="{{ route('home', ['categoria' => $categoria->slug]) }}#categoria-jogos"
                            @if ($categoriaSelecionada?->is($categoria)) aria-current="page" @endif>
                            <i class="{{ $categoria->icon }} me-1" aria-hidden="true"></i>
                            {{ $categoria->nome }}
                        </a>
                    @endforeach
                </nav>
            @else
                <p class="text-body-secondary">As categorias serão exibidas aqui em breve.</p>
            @endif

            @if ($categoriaSelecionada)
                <div id="categoria-jogos" class="pt-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                        <div>
                            <p class="text-body-secondary small mb-1">Categoria selecionada</p>
                            <h3 class="h4 fw-bold mb-0">
                                <i class="{{ $categoriaSelecionada->icon }} text-primary me-2" aria-hidden="true"></i>
                                {{ $categoriaSelecionada->nome }}
                            </h3>
                        </div>
                        <a class="link-secondary small" href="{{ route('home') }}#categories-heading">Limpar filtro</a>
                    </div>

                    @if ($jogosDaCategoria->isNotEmpty())
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                            @foreach ($jogosDaCategoria as $jogo)
                                <div class="col">
                                    <x-game-card :jogo="$jogo" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-body-secondary">Ainda não há jogos nesta categoria.</p>
                    @endif
                </div>
            @endif
        </section>

        <section aria-labelledby="promotions-heading" class="pb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
                <div>
                    <p class="text-primary text-uppercase fw-semibold small mb-1">Oportunidades por tempo limitado</p>
                    <h2 id="promotions-heading" class="h3 fw-bold mb-0">Jogos em promoção</h2>
                </div>
            </div>

            @if ($gruposEmPromocao->isNotEmpty())
                @foreach ($gruposEmPromocao as $grupo)
                    <div class="mb-4">
                        <h3 id="grupo-promocao-{{ $grupo->id }}" class="h4 fw-semibold mb-3">
                            {{ $grupo->nome }}
                        </h3>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3"
                            aria-labelledby="grupo-promocao-{{ $grupo->id }}">
                            @foreach ($grupo->promocoes as $promocao)
                                @if ($promocao->jogo)
                                    <div class="col">
                                        <x-game-card :jogo="$promocao->jogo" />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                <div class="rounded-4 border border-secondary bg-dark p-4 text-center text-body-secondary">
                    Não há promoções ativas no momento. Volte em breve para conferir novas ofertas.
                </div>
            @endif
        </section>
    </div>
@endsection
