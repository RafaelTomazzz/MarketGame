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

    public function test_homepage_renders_the_main_layout_without_active_promotions(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewIs('home');
        $response->assertSee('MarketGame');
        $response->assertSee('Buscar jogos...');
        $response->assertSee('id="site-search"', false);
        $response->assertSee('Jogos em promoção');
        $response->assertSee('Explore por categoria');
        $response->assertSee('Não há promoções ativas no momento.');
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

    public function test_homepage_defaults_to_action_category_games(): void
    {
        $acao = $this->criarCategoria('Ação', 'acao');
        $aventura = $this->criarCategoria('Aventura', 'aventura');
        $jogoDeAcao = $this->criarJogo('Jogo de ação', 'jogo-de-acao');
        $jogoDeAventura = $this->criarJogo('Jogo de aventura', 'jogo-de-aventura');
        $jogoDeAcao->categorias()->attach($acao);
        $jogoDeAventura->categorias()->attach($aventura);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewHas(
            'categoriaSelecionada',
            fn (?Categoria $categoria): bool => $categoria?->is($acao) ?? false,
        );
        $response->assertViewHas(
            'jogosDaCategoria',
            fn ($jogos): bool => $jogos->modelKeys() === [$jogoDeAcao->id],
        );
        $response->assertSee('Jogo de ação');
        $response->assertDontSee('Jogo de aventura');
    }

    public function test_homepage_lists_current_promotions_but_excludes_expired_ones(): void
    {
        $this->travelTo(now()->startOfDay());

        $categoria = $this->criarCategoria('Ação', 'acao');
        $jogoEmPromocao = $this->criarJogo('Jogo em oferta', 'jogo-em-oferta', '100.00');
        $jogoComOfertaExpirada = $this->criarJogo('Jogo com oferta expirada', 'jogo-com-oferta-expirada');
        $jogoEmPromocao->categorias()->attach($categoria);
        $jogoComOfertaExpirada->categorias()->attach($categoria);
        $grupoPromocaoId = $this->criarGrupoPromocao('Oferta de teste', 'oferta-de-teste');
        $this->criarPromocao($jogoEmPromocao, $grupoPromocaoId, '2027-01-01 23:59:59', '80.00');
        $this->criarPromocao($jogoComOfertaExpirada, $grupoPromocaoId, '2026-01-01 23:59:59', '60.00');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewHas(
            'gruposEmPromocao',
            fn ($grupos): bool => $grupos->mapWithKeys(
                fn ($grupo): array => [
                    $grupo->nome => $grupo->promocoes->map(fn ($promocao): string => $promocao->jogo->nome)->all(),
                ],
            )->all() === ['Oferta de teste' => ['Jogo em oferta']],
        );
        $response->assertSee('Oferta de teste');
        $response->assertSee('Jogo em oferta');
        $response->assertSee('R$ 80,00');
    }

    public function test_homepage_lists_games_under_their_promotion_group(): void
    {
        $grupoDeAcaoId = $this->criarGrupoPromocao('Ofertas de ação', 'ofertas-de-acao');
        $grupoDeAventuraId = $this->criarGrupoPromocao('Ofertas de aventura', 'ofertas-de-aventura');
        $jogoDeAcao = $this->criarJogo('Jogo de ação em oferta', 'jogo-de-acao-em-oferta');
        $outroJogoDeAcao = $this->criarJogo('Outro jogo de ação em oferta', 'outro-jogo-de-acao-em-oferta');
        $jogoDeAventura = $this->criarJogo('Jogo de aventura em oferta', 'jogo-de-aventura-em-oferta');
        $this->criarPromocao($jogoDeAcao, $grupoDeAcaoId, now()->addDay()->toDateTimeString(), '70.00');
        $this->criarPromocao($outroJogoDeAcao, $grupoDeAcaoId, now()->addDay()->toDateTimeString(), '60.00');
        $this->criarPromocao($jogoDeAventura, $grupoDeAventuraId, now()->addDay()->toDateTimeString(), '80.00');

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertViewHas(
            'gruposEmPromocao',
            fn ($grupos): bool => $grupos->count() === 2
                && $grupos->contains(
                    fn ($grupo): bool => $grupo->nome === 'Ofertas de ação'
                        && $grupo->promocoes->map(fn ($promocao): string => $promocao->jogo->nome)->all() === [
                            'Jogo de ação em oferta',
                            'Outro jogo de ação em oferta',
                        ],
                )
                && $grupos->contains(
                    fn ($grupo): bool => $grupo->nome === 'Ofertas de aventura'
                        && $grupo->promocoes->map(fn ($promocao): string => $promocao->jogo->nome)->all() === [
                            'Jogo de aventura em oferta',
                        ],
                ),
        );
        $response->assertSee('Ofertas de ação');
        $response->assertSee('Jogo de ação em oferta');
        $response->assertSee('Outro jogo de ação em oferta');
        $response->assertSee('Ofertas de aventura');
        $response->assertSee('Jogo de aventura em oferta');
    }

    public function test_carousel_shows_five_games_with_current_promotions_only(): void
    {
        $grupoPromocaoId = $this->criarGrupoPromocao('Oferta de teste', 'oferta-de-teste');
        $jogosEmPromocao = collect();

        for ($indice = 1; $indice <= 6; $indice++) {
            $jogo = $this->criarJogo("Oferta {$indice}", "oferta-{$indice}");
            $this->criarPromocao($jogo, $grupoPromocaoId, now()->addDay()->toDateTimeString(), '80.00');
            $jogosEmPromocao->push($jogo);
        }

        $jogoSemPromocao = $this->criarJogo('Jogo sem oferta', 'jogo-sem-oferta');
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertViewHas('jogosEmPromocao', fn ($jogos): bool => $jogos->count() === 5);

        preg_match_all(
            '/<article class="carousel-item(?: active)?\s*">.*?<\/article>/s',
            $response->getContent(),
            $slides,
        );

        $this->assertCount(5, $slides[0]);
        $this->assertStringNotContainsString($jogoSemPromocao->nome, implode('', $slides[0]));

        foreach ($slides[0] as $slide) {
            $this->assertStringContainsString('Em promoção', $slide);
            $nomesEncontrados = $jogosEmPromocao
                ->filter(fn (Jogo $jogo): bool => str_contains($slide, $jogo->nome));

            $this->assertCount(1, $nomesEncontrados);
        }
    }

    private function criarCategoria(string $nome, string $slug): Categoria
    {
        return Categoria::query()->create([
            'nome' => $nome,
            'slug' => $slug,
            'icon' => 'bi bi-controller',
        ]);
    }

    private function criarGrupoPromocao(string $nome, string $slug): int
    {
        return DB::table('grupo_promocoes')->insertGetId([
            'nome' => $nome,
            'slug' => $slug,
            'descricao' => "Grupo {$nome} usado no teste da página inicial.",
            'created_at' => now(),
            'updated_at' => now(),
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
