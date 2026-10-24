@extends('layouts.black-layout')

@section('title', 'Criar conta | MarketGame')
@section('layout-class', 'mg-auth-page--scroll-card')

@section('content')
    <header class="text-center mb-4">
        <a class="d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold fs-3 text-primary" href="{{ url('/') }}">
            <img src="{{ asset('img/estrela_logo.svg') }}" alt="" width="36" height="34" aria-hidden="true">
            <span class="fw-light">MarketGame</span>
        </a>
        <h1 class="h4 mt-3 mb-2">Crie sua conta</h1>
        <p class="text-body-secondary mb-0">Cadastre-se para explorar o catálogo.</p>
    </header>

    @error('cadastro')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('cadastro.store') }}">
        @csrf

        <div class="mb-3">
            <label for="nome" class="form-label">Nome completo</label>
            <input
                id="nome"
                name="nome"
                type="text"
                class="form-control @error('nome') is-invalid @enderror"
                value="{{ old('nome') }}"
                autocomplete="name"
                maxlength="20"
                required
                autofocus
            >
            @error('nome')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="cpf" class="form-label">CPF</label>
            <input
                id="cpf"
                name="cpf"
                type="text"
                class="form-control @error('cpf') is-invalid @enderror"
                value="{{ old('cpf') }}"
                inputmode="numeric"
                autocomplete="off"
                placeholder="000.000.000-00"
                maxlength="14"
                aria-describedby="cpf-format"
                required
            >
            <div id="cpf-format" class="form-text">Digite os 11 números do CPF.</div>
            @error('cpf')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="telefone" class="form-label">Telefone</label>
            <input
                id="telefone"
                name="telefone"
                type="tel"
                class="form-control @error('telefone') is-invalid @enderror"
                value="{{ old('telefone') }}"
                autocomplete="tel"
                placeholder="(00) 00000-0000"
                inputmode="tel"
                maxlength="15"
                required
            >
            @error('telefone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                autocomplete="email"
                required
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <fieldset class="border rounded-3 p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6 fw-semibold">Endereço</legend>

            <div class="mb-3">
                <label for="cep" class="form-label">CEP</label>
                <input
                    id="cep"
                    name="cep"
                    type="text"
                    class="form-control @error('cep') is-invalid @enderror"
                    value="{{ old('cep') }}"
                    autocomplete="postal-code"
                    inputmode="numeric"
                    placeholder="00000-000"
                    maxlength="9"
                    required
                >
                @error('cep')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="uf" class="form-label">Estado (UF)</label>
                <input
                    id="uf"
                    name="uf"
                    type="text"
                    class="form-control @error('uf') is-invalid @enderror"
                    value="{{ old('uf') }}"
                    autocomplete="address-level1"
                    autocapitalize="characters"
                    pattern="[A-Z]{2}"
                    title="Informe a UF com duas letras maiúsculas."
                    maxlength="2"
                    required
                >
                @error('uf')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="cidade" class="form-label">Cidade</label>
                <input
                    id="cidade"
                    name="cidade"
                    type="text"
                    class="form-control @error('cidade') is-invalid @enderror"
                    value="{{ old('cidade') }}"
                    autocomplete="address-level2"
                    required
                >
                @error('cidade')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="bairro" class="form-label">Bairro</label>
                <input
                    id="bairro"
                    name="bairro"
                    type="text"
                    class="form-control @error('bairro') is-invalid @enderror"
                    value="{{ old('bairro') }}"
                    autocomplete="address-level3"
                    required
                >
                @error('bairro')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="rua" class="form-label">Rua</label>
                <input
                    id="rua"
                    name="rua"
                    type="text"
                    class="form-control @error('rua') is-invalid @enderror"
                    value="{{ old('rua') }}"
                    autocomplete="address-line1"
                    required
                >
                @error('rua')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="numero" class="form-label">Número</label>
                <input
                    id="numero"
                    name="numero"
                    type="text"
                    class="form-control @error('numero') is-invalid @enderror"
                    value="{{ old('numero') }}"
                    autocomplete="off"
                    required
                >
                @error('numero')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="complemento" class="form-label">Complemento <span class="text-body-secondary">(opcional)</span></label>
                <input
                    id="complemento"
                    name="complemento"
                    type="text"
                    class="form-control @error('complemento') is-invalid @enderror"
                    value="{{ old('complemento') }}"
                    autocomplete="address-line2"
                >
                @error('complemento')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </fieldset>

        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                autocomplete="new-password"
                required
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirme a senha</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                class="form-control"
                autocomplete="new-password"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Criar conta</button>
    </form>

    <p class="text-center text-body-secondary mt-4 mb-0">
        Já tem cadastro?
        <a class="link-primary fw-semibold text-decoration-none" href="{{ url('/login') }}">Entrar</a>
    </p>
@endsection
