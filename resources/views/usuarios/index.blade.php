@extends('layouts.main-layout')

@section('title', 'Usuários | MarketGame')

@section('content')
    <div class="container py-4 py-lg-5">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="text-primary text-uppercase fw-semibold small mb-1">Área de testes</p>
                <h1 class="h2 fw-bold mb-0">Usuários cadastrados</h1>
            </div>
            <p class="text-body-secondary mb-0">
                {{ $usuarios->total() }} {{ $usuarios->total() === 1 ? 'usuário' : 'usuários' }}
            </p>
        </div>

        @if ($usuarios->isNotEmpty())
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                @foreach ($usuarios as $usuario)
                    <div class="col">
                        <article class="card h-100 border-secondary bg-dark shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <h2 class="h5 fw-bold mb-0">{{ $usuario->nome }}</h2>
                                    <span class="badge text-bg-secondary">#{{ $usuario->id }}</span>
                                </div>

                                <dl class="row mb-0">
                                    <dt class="col-sm-4 text-body-secondary">E-mail</dt>
                                    <dd class="col-sm-8 text-break">{{ $usuario->email }}</dd>

                                    <dt class="col-sm-4 text-body-secondary">CPF</dt>
                                    <dd class="col-sm-8">{{ $usuario->cpf }}</dd>

                                    <dt class="col-sm-4 text-body-secondary">Telefone</dt>
                                    <dd class="col-sm-8">{{ $usuario->telefone }}</dd>

                                    <dt class="col-sm-4 text-body-secondary">Cadastro</dt>
                                    <dd class="col-sm-8">
                                        {{ $usuario->created_at?->format('d/m/Y H:i') ?? 'Não disponível' }}
                                    </dd>

                                    <dt class="col-sm-4 text-body-secondary">Endereço</dt>
                                    <dd class="col-sm-8 mb-0">
                                        @forelse ($usuario->enderecos as $endereco)
                                            <address class="mb-2">
                                                {{ $endereco->rua }}, {{ $endereco->numero }}
                                                @if ($endereco->complemento)
                                                    - {{ $endereco->complemento }}
                                                @endif
                                                <br>
                                                {{ $endereco->bairro }}, {{ $endereco->cidade }} - {{ $endereco->uf }}
                                                <br>
                                                CEP: {{ $endereco->cep }}
                                            </address>
                                        @empty
                                            <span class="text-body-secondary">Não cadastrado</span>
                                        @endforelse
                                    </dd>
                                </dl>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $usuarios->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="rounded-4 border border-secondary bg-dark p-5 text-center">
                <p class="h5 mb-2">Nenhum usuário cadastrado</p>
                <p class="text-body-secondary mb-0">Os usuários aparecerão aqui quando forem cadastrados.</p>
            </div>
        @endif
    </div>
@endsection
