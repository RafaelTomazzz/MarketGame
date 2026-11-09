<?php

namespace Tests\Feature\Console;

use App\Models\Jogo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CadastrarDadosTesteTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_one_user_and_one_game_with_related_test_data(): void
    {
        $this->artisan('app:cadastrar-dados-teste')
            ->expectsOutputToContain('Dados fictícios cadastrados com sucesso:')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('jogos', 1);
        $this->assertDatabaseCount('enderecos', 1);
        $this->assertDatabaseCount('categorias', 1);

        $this->assertSame(1, User::query()->sole()->enderecos()->count());
        $this->assertSame(1, Jogo::query()->sole()->categorias()->count());
    }
}
