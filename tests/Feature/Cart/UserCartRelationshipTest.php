<?php

namespace Tests\Feature\Cart;

use App\Models\Jogo;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class UserCartRelationshipTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_cart_relationship_keeps_duplicate_game_rows_as_individual_units(): void
    {
        $user = $this->criarUsuario('dono@example.test');
        $outroUsuario = $this->criarUsuario('outro@example.test');
        $jogo = $this->criarJogo();

        $user->jogosNoCarrinho()->attach($jogo->id);
        $user->jogosNoCarrinho()->attach($jogo->id);
        $outroUsuario->jogosNoCarrinho()->attach($jogo->id);

        $this->assertDatabaseCount('jogo_user', 3);
        $this->assertSame([$jogo->id, $jogo->id], $user->jogosNoCarrinho->modelKeys());
        $this->assertSame([$jogo->id], $outroUsuario->jogosNoCarrinho->modelKeys());
        $this->assertSame(2, $jogo->usuariosComJogoNoCarrinho()->whereKey($user->id)->count());
    }

    private function criarUsuario(string $email): User
    {
        return User::query()->create([
            'nome' => 'Usuário de teste',
            'email' => $email,
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => 'senha-segura',
        ]);
    }

    private function criarJogo(): Jogo
    {
        return Jogo::query()->create([
            'nome' => 'Jogo de teste',
            'slug' => 'jogo-de-teste',
            'descricao' => 'Descrição do jogo de teste.',
            'preco' => '59.90',
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
        ]);
    }
}
