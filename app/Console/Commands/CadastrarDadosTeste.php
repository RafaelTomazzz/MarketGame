<?php

namespace App\Console\Commands;

use App\Models\Jogo;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('bot:cadastrar-dados-teste')]
#[Description('Cadastra um usuário e um jogo fictícios para testes.')]
class CadastrarDadosTeste extends Command
{
    public function handle(): int
    {
        $usuario = User::factory()->create();
        $jogo = Jogo::factory()->create();

        $this->info("Dados fictícios cadastrados com sucesso: usuário #{$usuario->id}, jogo #{$jogo->id}.");

        return self::SUCCESS;
    }
}
