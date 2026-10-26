<?php

namespace App\Services;

use App\Models\FormaPagamento;
use App\Models\User;

class FormaPagamentoService
{
    /**
     * @param array{
     *     tipo: string,
     *     numero_cartao?: string,
     *     titular_cartao?: string,
     *     bandeira?: string,
     *     validade?: string
     * } $dados
     */
    public function store(User $usuario, array $dados): FormaPagamento
    {
        $nome = match ($dados['tipo']) {
            'debito' => 'Cartão de débito',
            'credito' => 'Cartão de crédito',
            'pix' => 'Pix',
        };

        $atributos = [
            'nome' => $nome,
            'tipo' => $dados['tipo'],
        ];

        if ($dados['tipo'] !== 'pix') {
            $ultimosDigitos = substr($dados['numero_cartao'], -4);

            $atributos = [
                ...$atributos,
                'nome' => $nome.' final '.$ultimosDigitos,
                'titular' => $dados['titular_cartao'],
                'bandeira' => $dados['bandeira'],
                'ultimos_digitos' => $ultimosDigitos,
                'validade' => $dados['validade'].'-01',
            ];
        }

        return $usuario->formasPagamento()->create($atributos);
    }
}
