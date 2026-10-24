<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function login(): View
    {
        return view('auth.login');
    }

    public function cadastro(): View
    {
        return view('auth.cadastro');
    }

    public function authenticate(LoginRequest $request): RedirectResponse
    {
        try {
            if (! $this->authService->login($request->validated())) {
                return back()
                    ->withErrors(['login' => 'E-mail ou senha inválidos.'])
                    ->withInput($request->safe()->only('email'));
            }

            return redirect()->intended('/');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['login' => 'Não foi possível realizar o login. Tente novamente.'])
                ->withInput($request->safe()->only('email'));
        }
    }

    public function cadastrar(RegisterRequest $request): RedirectResponse
    {
        try {
            $this->authService->registrarUsuario($request->validated());

            return redirect()
                ->route('login')
                ->with('status', 'Cadastro realizado com sucesso. Faça login para continuar.');
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['cadastro' => 'Não foi possível realizar o cadastro. Tente novamente.'])
                ->withInput($request->safe()->except([
                    'cpf',
                    'telefone',
                    'password',
                    'password_confirmation',
                ]));
        }
    }
}
