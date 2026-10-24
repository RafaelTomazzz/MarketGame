@props(['jogo'])

<article class="card h-100 border-secondary bg-dark shadow-sm">
    <div class="ratio ratio-21x9 bg-secondary overflow-hidden rounded-top">
        @if ($jogo->imagem)
            <img class="w-100 h-100 object-fit-cover" src="{{ $jogo->imagem }}" alt="Capa de {{ $jogo->nome }}"
                loading="lazy">
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

        <h3 class="h5 fw-semibold mb-2">{{ $jogo->nome }}</h3>

        @if ($jogo->categorias->isNotEmpty())
            <div class="d-flex flex-wrap gap-1 mb-3">
                @foreach ($jogo->categorias->take(2) as $categoria)
                    <span class="badge rounded-pill text-bg-secondary">{{ $categoria->nome }}</span>
                @endforeach
            </div>
        @endif

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
