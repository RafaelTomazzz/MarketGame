<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class AuthService
{
    public function __construct(private EnderecoService $enderecoService) {}

    /**
     * @param  array<string, mixed>  $credenciais
     */
    public function login(array $credenciais): bool
    {
        try {
            if (! Auth::attempt(Arr::only($credenciais, ['email', 'password']))) {
                return false;
            }

            request()->session()->regenerate();

            return true;
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível realizar o login.', previous: $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function registrarUsuario(array $dados): User
    {
        return DB::transaction(function () use ($dados): User {
            $usuario = User::create(Arr::only($dados, [
                'nome',
                'email',
                'cpf',
                'telefone',
                'password',
            ]));

            $this->enderecoService->criarEndereco($usuario, $dados);

            return $usuario;
        });
    }
}
