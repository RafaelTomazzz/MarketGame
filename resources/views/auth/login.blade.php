@extends('layouts.black-layout')

@section('title', 'Entrar | MarketGame')

@section('content')
    <header class="text-center mb-4">
        <a class="d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold fs-3 text-primary" href="{{ url('/') }}">
            <img src="{{ asset('img/estrela_logo.svg') }}" alt="" width="36" height="34" aria-hidden="true">
            <span class="fw-light">MarketGame</span>
        </a>
        <h1 class="h4 mt-3 mb-2">Boas-vindas de volta</h1>
        <p class="text-body-secondary mb-0">Entre na sua conta para continuar.</p>
    </header>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @error('login')
        <div class="alert alert-danger" role="alert">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.authenticate') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email') }}"
                autocomplete="email"
                placeholder="voce@exemplo.com"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Senha</label>
            <input
                id="password"
                name="password"
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                autocomplete="current-password"
                placeholder="Digite sua senha"
                required
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-semibold">Entrar</button>
    </form>

    <p class="text-center text-body-secondary mt-4 mb-0">
        Ainda não tem cadastro?
        <a class="link-primary fw-semibold text-decoration-none" href="{{ url('/cadastro') }}">Criar conta</a>
    </p>
@endsection
