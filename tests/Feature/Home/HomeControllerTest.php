<?php

namespace Tests\Feature\Home;

use App\Models\Categoria;
use App\Models\Jogo;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_homepage_renders_the_main_layout_and_home_view(): void
    {
        $categoria = $this->criarCategoria('Aventura', 'aventura');
        $jogo = $this->criarJogo('Jogo em destaque', 'jogo-em-destaque', '149.90');
        $jogo->categorias()->attach($categoria);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home');
        $response->assertSee('MarketGame');
        $response->assertSee('Buscar jogos...');
        $response->assertSee('id="site-search"', false);
        $response->assertSee('Jogos em destaque');
        $response->assertSee('Explore por categoria');
        $response->assertSee('Jogos em promoção');
        $response->assertSee('Jogo em destaque');
        $response->assertSee('Jogos físicos para quem ama jogar.');
    }

    public function test_selecting_a_category_lists_only_its_games(): void
    {
        $aventura = $this->criarCategoria('Aventura', 'aventura');
        $terror = $this->criarCategoria('Terror', 'terror');
        $jogoDeAventura = $this->criarJogo('Jogo de aventura', 'jogo-de-aventura');
        $jogoDeTerror = $this->criarJogo('Jogo de terror', 'jogo-de-terror');
        $jogoDeAventura->categorias()->attach($aventura);
        $jogoDeTerror->categorias()->attach($terror);

        $response = $this->get(route('home', ['categoria' => 'aventura']));

        $response->assertOk();
        $response->assertViewHas(
            'categoriaSelecionada',
            fn (?Categoria $categoria): bool => $categoria?->is($aventura) ?? false,
        );
        $response->assertViewHas(
            'jogosDaCategoria',
            fn ($jogos): bool => $jogos->modelKeys() === [$jogoDeAventura->id],
        );
        $response->assertSee('Jogo de aventura');
    }

    public function test_homepage_lists_current_promotions_but_excludes_expired_ones(): void
    {
        $this->travelTo(now()->startOfDay());

        $categoria = $this->criarCategoria('Ação', 'acao');
        $jogoEmPromocao = $this->criarJogo('Jogo em oferta', 'jogo-em-oferta', '100.00');
        $jogoComOfertaExpirada = $this->criarJogo('Jogo com oferta expirada', 'jogo-com-oferta-expirada');
        $jogoEmPromocao->categorias()->attach($categoria);
        $jogoComOfertaExpirada->categorias()->attach($categoria);
        $grupoPromocaoId = DB::table('grupo_promocoes')->insertGetId([
            'nome' => 'Oferta de teste',
            'slug' => 'oferta-de-teste',
            'descricao' => 'Grupo promocional usado no teste da página inicial.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->criarPromocao($jogoEmPromocao, $grupoPromocaoId, '2027-01-01 23:59:59', '80.00');
        $this->criarPromocao($jogoComOfertaExpirada, $grupoPromocaoId, '2026-01-01 23:59:59', '60.00');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewHas(
            'jogosEmPromocao',
            fn ($jogos): bool => $jogos->modelKeys() === [$jogoEmPromocao->id],
        );
        $response->assertSee('Jogo em oferta');
        $response->assertSee('R$ 80,00');
    }

    private function criarCategoria(string $nome, string $slug): Categoria
    {
        return Categoria::query()->create([
            'nome' => $nome,
            'slug' => $slug,
            'icon' => 'bi bi-controller',
        ]);
    }

    private function criarJogo(string $nome, string $slug, string $preco = '99.90'): Jogo
    {
        return Jogo::query()->create([
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => "Descrição de {$nome}.",
            'preco' => $preco,
            'imagem' => null,
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
        ]);
    }

    private function criarPromocao(Jogo $jogo, int $grupoPromocaoId, string $dataFim, string $valor): void
    {
        DB::table('promocoes')->insert([
            'jogo_id' => $jogo->id,
            'grupo_promocao_id' => $grupoPromocaoId,
            'tipo' => 'percentual',
            'percentual' => 20,
            'data_fim' => $dataFim,
            'valor' => $valor,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
