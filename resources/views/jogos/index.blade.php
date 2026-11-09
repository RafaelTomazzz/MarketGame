@extends('layouts.main-layout')

@section('title', 'Jogos | MarketGame')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-primary text-uppercase fw-semibold small mb-1">Área de testes</p>
                <h1 class="h2 fw-bold mb-0">Jogos cadastrados</h1>
            </div>
            <p class="text-body-secondary mb-0">
                {{ $jogos->total() }} {{ $jogos->total() === 1 ? 'jogo' : 'jogos' }}
            </p>
        </div>

        @if ($jogos->isNotEmpty())
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                @foreach ($jogos as $jogo)
                    <div class="col">
                        <article class="card h-100 border-secondary bg-dark shadow-sm">
                            <div class="ratio ratio-21x9 bg-secondary overflow-hidden rounded-top">
                                @if ($jogo->imagem)
                                    <img class="w-100 h-100 object-fit-cover" src="{{ $jogo->imagem }}"
                                        alt="Capa de {{ $jogo->nome }}" loading="lazy">
                                @else
                                    <div class="d-flex align-items-center justify-content-center text-body-secondary">
                                        <i class="bi bi-controller fs-1" aria-hidden="true"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="card-body d-flex flex-column">
                                @if ($jogo->promocao)
                                    <div class="mb-2">
                                        @if ($jogo->promocao->percentual)
                                            <span class="badge text-bg-danger">-{{ $jogo->promocao->percentual }}%</span>
                                        @else
                                            <span class="badge text-bg-danger">
                                                -R$ {{ number_format((float) $jogo->preco - (float) $jogo->promocao->valor, 2, ',', '.') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <h2 class="h5 fw-semibold mb-1">{{ $jogo->nome }}</h2>
                                <p class="small text-body-secondary mb-3">{{ $jogo->desenvolvedor }}</p>

                                @if ($jogo->categorias->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1 mb-3">
                                        @foreach ($jogo->categorias as $categoria)
                                            <span class="badge rounded-pill text-bg-secondary">{{ $categoria->nome }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                <p class="mb-3">{{ $jogo->descricao }}</p>

                                <dl class="row small mb-3">
                                    <dt class="col-5 text-body-secondary">Lançamento</dt>
                                    <dd class="col-7">{{ $jogo->data_lancamento?->format('d/m/Y') ?? 'Não informado' }}</dd>

                                    <dt class="col-5 text-body-secondary">Cadastrado</dt>
                                    <dd class="col-7">{{ $jogo->created_at?->format('d/m/Y H:i') ?? 'Não informado' }}</dd>
                                </dl>

                                <div class="mt-auto">
                                    @if ($jogo->promocao)
                                        <p class="small text-body-secondary text-decoration-line-through mb-0">
                                            R$ {{ number_format((float) $jogo->preco, 2, ',', '.') }}
                                        </p>
                                        <p class="fs-5 fw-bold text-primary mb-0">
                                            R$ {{ number_format((float) $jogo->promocao->valor, 2, ',', '.') }}
                                        </p>
                                    @else
                                        <p class="fs-5 fw-bold text-primary mb-0">
                                            R$ {{ number_format((float) $jogo->preco, 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $jogos->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="rounded-4 border border-secondary bg-dark p-5 text-center">
                <p class="h5 mb-2">Nenhum jogo cadastrado</p>
                <p class="text-body-secondary mb-0">Os jogos aparecerão aqui quando forem cadastrados.</p>
            </div>
        @endif
    </div>
@endsection
