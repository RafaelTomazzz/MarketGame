<?php

namespace Tests\Feature\Games;

use App\Models\Categoria;
use App\Models\Jogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class JogoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_game_cards_ordered_by_registration_date(): void
    {
        $oldGame = $this->criarJogo('Jogo antigo', 'jogo-antigo');
        $newGame = $this->criarJogo('Jogo recente', 'jogo-recente');
        $category = Categoria::query()->create([
            'nome' => 'Aventura',
            'slug' => 'aventura',
            'icon' => 'bi bi-controller',
        ]);
        $newGame->categorias()->attach($category);
        $oldDate = now()->subDays(30);
        $newDate = now()->subDay();
        $oldGame->forceFill(['created_at' => $oldDate, 'updated_at' => $oldDate])->save();
        $newGame->forceFill(['created_at' => $newDate, 'updated_at' => $newDate])->save();

        $response = $this->get(route('jogos.index'));

        $response->assertOk();
        $response->assertViewIs('jogos.index');
        $response->assertViewHas(
            'jogos',
            fn ($jogos): bool => $jogos->getCollection()->modelKeys() === [$newGame->id, $oldGame->id],
        );
        $response->assertSeeText('Jogo recente');
        $response->assertSeeText('Aventura');
        $response->assertSeeText('R$ 99,90');
    }

    public function test_guest_can_navigate_to_the_remaining_game_cards(): void
    {
        $oldGame = $this->criarJogo('Jogo antigo', 'jogo-antigo');
        $oldDate = now()->subDays(30);
        $oldGame->forceFill(['created_at' => $oldDate, 'updated_at' => $oldDate])->save();

        for ($index = 1; $index <= 15; $index++) {
            $game = $this->criarJogo("Jogo {$index}", "jogo-{$index}");
            $createdAt = now()->subDays(16 - $index);
            $game->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
        }

        $firstPage = $this->get(route('jogos.index'));
        $secondPage = $this->get(route('jogos.index', ['page' => 2]));

        $firstPage->assertOk();
        $firstPage->assertSeeText('Jogo 15');
        $firstPage->assertDontSeeText('Jogo antigo');
        $secondPage->assertOk();
        $secondPage->assertSeeText('Jogo antigo');
    }

    public function test_game_cards_only_show_current_promotions(): void
    {
        $activeGame = $this->criarJogo('Jogo em promoção', 'jogo-em-promocao');
        $expiredGame = $this->criarJogo('Jogo com promoção vencida', 'jogo-promocao-vencida');
        $groupId = DB::table('grupo_promocoes')->insertGetId([
            'nome' => 'Promoção de teste',
            'slug' => 'promocao-de-teste',
            'descricao' => 'Grupo para testar promoções dos jogos.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->criarPromocao($activeGame, $groupId, now()->addDay()->toDateTimeString());
        $this->criarPromocao($expiredGame, $groupId, now()->subDay()->toDateTimeString());

        $response = $this->get(route('jogos.index'));

        $response->assertOk();
        $response->assertViewHas(
            'jogos',
            fn ($jogos): bool => $jogos->getCollection()
                ->firstWhere('id', $activeGame->id)?->promocao !== null
                && $jogos->getCollection()
                    ->firstWhere('id', $expiredGame->id)?->promocao === null,
        );
        $response->assertSeeText('Jogo em promoção');
        $response->assertSeeText('-20%');
        $response->assertSeeText('Jogo com promoção vencida');
        $response->assertSeeText('R$ 80,00');
    }

    private function criarJogo(string $nome, string $slug): Jogo
    {
        return Jogo::query()->create([
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => "Descrição de {$nome}.",
            'preco' => '99.90',
            'imagem' => null,
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
        ]);
    }

    private function criarPromocao(Jogo $jogo, int $groupId, string $dataFim): void
    {
        DB::table('promocoes')->insert([
            'jogo_id' => $jogo->id,
            'grupo_promocao_id' => $groupId,
            'tipo' => 'percentual',
            'percentual' => 20,
            'data_fim' => $dataFim,
            'valor' => '80.00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
