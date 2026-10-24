<?php

namespace App\Services;

use App\Models\Endereco;
use App\Models\User;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class EnderecoService
{
    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarEndereco(User $usuario, array $dados): Endereco
    {
        try {
            return $usuario->enderecos()->create(Arr::only($dados, [
                'uf',
                'cep',
                'cidade',
                'bairro',
                'rua',
                'numero',
                'complemento',
            ]));
        } catch (Throwable $exception) {
            throw new RuntimeException('Não foi possível criar o endereço.', previous: $exception);
        }
    }
}
