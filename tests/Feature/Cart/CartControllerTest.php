<?php

namespace Tests\Feature\Cart;

use App\Models\Categoria;
use App\Models\GrupoPromocao;
use App\Models\Jogo;
use App\Models\Promocao;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_the_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_each_unit_in_pivot_order_with_a_server_calculated_subtotal(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $outroJogo = $this->criarJogo('outro-jogo', '10.00');
        $this->associarCategoria($jogo);
        $this->associarCategoria($outroJogo);

        $user->jogosNoCarrinho()->attach($jogo->id, [
            'created_at' => '2026-01-02 12:00:00',
            'updated_at' => '2026-01-02 12:00:00',
        ]);
        $user->jogosNoCarrinho()->attach($jogo->id, [
            'created_at' => '2026-01-02 13:00:00',
            'updated_at' => '2026-01-02 13:00:00',
        ]);
        $user->jogosNoCarrinho()->attach($outroJogo->id, [
            'created_at' => '2026-01-02 11:00:00',
            'updated_at' => '2026-01-02 11:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewIs('cart.index');
        $response->assertSee('Meu carrinho');
        $response->assertSee('Resumo do pedido');
        $response->assertSee('Subtotal (3 itens)');
        $response->assertSee('R$ 129,80');
        $response->assertSee('R$ 5,00');
        $response->assertSee('R$ 134,80');
        $response->assertSee('disabled', false);
        $response->assertSee('Finalizar pedido');
        $response->assertViewHas('carrinhoItens', function (Collection $itens) use ($jogo, $outroJogo): bool {
            return $itens->modelKeys() === [$outroJogo->id, $jogo->id, $jogo->id]
                && $itens->every(
                    fn (Jogo $item): bool => $item->relationLoaded('categorias')
                        && $item->relationLoaded('promocao')
                        && $item->pivot->id !== null,
                );
        });
        $response->assertViewHas('subtotal', fn (string $subtotal): bool => $subtotal === '129.80');
    }

    public function test_cart_page_lists_only_the_authenticated_users_payment_methods_in_name_and_id_order(): void
    {
        $user = $this->criarUsuario();
        $outroUsuario = $this->criarUsuario('outro-metodo@example.test');
        $metodoPixAntigo = $user->formasPagamento()->create(['nome' => 'Pix']);
        $metodoCartao = $user->formasPagamento()->create(['nome' => 'Cartão']);
        $metodoPixNovo = $user->formasPagamento()->create(['nome' => 'Pix']);
        $outroUsuario->formasPagamento()->create(['nome' => 'A - método alheio']);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewHas('formasPagamento', function (Collection $formasPagamento) use (
            $metodoCartao,
            $metodoPixAntigo,
            $metodoPixNovo,
        ): bool {
            return $formasPagamento->modelKeys() === [
                $metodoCartao->id,
                $metodoPixAntigo->id,
                $metodoPixNovo->id,
            ];
        });
        $response->assertSeeText('Cartão');
        $response->assertSeeText('Pix');
        $response->assertDontSeeText('A - método alheio');
        $response->assertSee('name="forma_pagamento_id"', false);
        $response->assertSee(route('payment-methods.store'), false);
        $response->assertSee('Adicionar método');
        $response->assertSee('Cartão de débito');
        $response->assertSee('Cartão de crédito');
        $response->assertSee('Pix');
        $response->assertSee('id="card-details"', false);
        $response->assertSee('name="numero_cartao"', false);
        $response->assertSee('name="validade"', false);
        $response->assertSee('não são armazenados.', false);
        $response->assertSee(route('orders.store'), false);
    }

    public function test_active_promotion_final_value_is_used_for_each_cart_unit(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $this->associarCategoria($jogo);
        $this->criarPromocao($jogo, '39.90', '2026-10-27 12:00:00');
        $user->jogosNoCarrinho()->attach($jogo->id);
        $user->jogosNoCarrinho()->attach($jogo->id);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewHas('carrinhoItens', function (Collection $itens) use ($jogo): bool {
            return $itens->modelKeys() === [$jogo->id, $jogo->id]
                && $itens->every(fn (Jogo $item): bool => $item->promocao?->valor === '39.90');
        });
        $response->assertViewHas('subtotal', fn (string $subtotal): bool => $subtotal === '79.80');
    }

    public function test_expired_and_missing_promotions_use_regular_prices(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $user = $this->criarUsuario();
        $jogoComPromocaoExpirada = $this->criarJogo('promocao-expirada', '29.90');
        $jogoSemPromocao = $this->criarJogo('sem-promocao', '15.00');
        $this->associarCategoria($jogoComPromocaoExpirada);
        $this->associarCategoria($jogoSemPromocao);
        $this->criarPromocao($jogoComPromocaoExpirada, '5.00', '2026-10-25 12:00:00');
        $user->jogosNoCarrinho()->attach($jogoComPromocaoExpirada->id);
        $user->jogosNoCarrinho()->attach($jogoSemPromocao->id);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertViewHas('carrinhoItens', function (Collection $itens): bool {
            return $itens->every(fn (Jogo $item): bool => $item->relationLoaded('promocao'))
                && $itens->firstWhere('slug', 'promocao-expirada')->promocao === null
                && $itens->firstWhere('slug', 'sem-promocao')->promocao === null;
        });
        $response->assertViewHas('subtotal', fn (string $subtotal): bool => $subtotal === '44.90');
    }

    public function test_guest_is_redirected_to_login_without_adding_the_game(): void
    {
        $jogo = $this->criarJogo();

        $response = $this->from(route('home'))->post(route('cart.add', $jogo));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('jogo_user', 0);
    }

    public function test_authenticated_user_gets_a_new_cart_row_for_each_addition(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();

        $firstResponse = $this->actingAs($user)
            ->from(route('home'))
            ->post(route('cart.add', $jogo));

        $firstResponse->assertRedirect(route('home'));
        $firstResponse->assertSessionHas('success', 'Jogo adicionado ao carrinho com sucesso.');
        $firstResponse->assertSessionHas('cart_added_game_id', $jogo->id);
        $this->assertDatabaseCount('jogo_user', 1);

        $secondResponse = $this->post(route('cart.add', $jogo));

        $secondResponse->assertRedirect(route('home'));
        $secondResponse->assertSessionHas('success', 'Jogo adicionado ao carrinho com sucesso.');
        $secondResponse->assertSessionHas('cart_added_game_id', $jogo->id);
        $this->assertDatabaseCount('jogo_user', 2);
        $this->assertSame([$jogo->id, $jogo->id], $user->jogosNoCarrinho->modelKeys());
    }

    public function test_authenticated_request_returns_not_found_for_a_missing_game(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->post(route('cart.add', 999999));

        $response->assertNotFound();
        $this->assertDatabaseCount('jogo_user', 0);
    }

    public function test_guest_is_redirected_to_login_without_removing_a_cart_unit(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);
        $itemId = DB::table('jogo_user')->value('id');

        $response = $this->delete(route('cart.remove', $itemId));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('jogo_user', ['id' => $itemId, 'user_id' => $user->id]);
    }

    public function test_authenticated_user_removes_only_the_selected_duplicate_unit(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);
        $user->jogosNoCarrinho()->attach($jogo->id);
        $itemIds = DB::table('jogo_user')->orderBy('id')->pluck('id');

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->delete(route('cart.remove', $itemIds[0]));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Item removido do carrinho com sucesso.');
        $this->assertDatabaseCount('jogo_user', 1);
        $this->assertDatabaseHas('jogo_user', ['id' => $itemIds[1], 'user_id' => $user->id]);
        $this->assertDatabaseMissing('jogo_user', ['id' => $itemIds[0]]);
    }

    public function test_user_cannot_remove_another_users_cart_unit(): void
    {
        $user = $this->criarUsuario();
        $outroUsuario = $this->criarUsuario('outro-carrinho@example.test');
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);
        $outroUsuario->jogosNoCarrinho()->attach($jogo->id);
        $itemIdDoOutroUsuario = DB::table('jogo_user')
            ->where('user_id', $outroUsuario->id)
            ->value('id');

        $response = $this->actingAs($user)
            ->delete(route('cart.remove', $itemIdDoOutroUsuario));

        $response->assertNotFound();
        $this->assertDatabaseCount('jogo_user', 2);
        $this->assertDatabaseHas('jogo_user', [
            'id' => $itemIdDoOutroUsuario,
            'user_id' => $outroUsuario->id,
        ]);
        $this->assertDatabaseHas('jogo_user', ['user_id' => $user->id, 'jogo_id' => $jogo->id]);
    }

    public function test_header_displays_the_authenticated_users_cart_unit_count(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id, ['created_at' => now(), 'updated_at' => now()]);
        $user->jogosNoCarrinho()->attach($jogo->id, ['created_at' => now(), 'updated_at' => now()]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('aria-label="Carrinho: 2 itens"', false);
        $this->assertMatchesRegularExpression(
            '/<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">\s*2\s*<span class="visually-hidden">itens no carrinho<\/span>/s',
            $response->getContent(),
        );
    }

    public function test_header_displays_an_empty_cart_count_for_guests(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('aria-label="Carrinho: 0 itens"', false);
    }

    private function criarUsuario(string $email = 'carrinho@example.test'): User
    {
        return User::query()->create([
            'nome' => 'Usuário de teste',
            'email' => $email,
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => 'senha-segura',
        ]);
    }

    private function criarJogo(string $slug = 'jogo-de-teste', string $preco = '59.90'): Jogo
    {
        return Jogo::query()->create([
            'nome' => $slug,
            'slug' => $slug,
            'descricao' => 'Descrição do jogo de teste.',
            'preco' => $preco,
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
        ]);
    }

    private function associarCategoria(Jogo $jogo): void
    {
        $categoria = Categoria::query()->create([
            'nome' => 'Categoria de teste',
            'slug' => 'categoria-de-teste-'.$jogo->id,
            'icon' => 'bi-controller',
        ]);

        $jogo->categorias()->attach($categoria->id);
    }

    private function criarPromocao(Jogo $jogo, string $valor, string $dataFim): Promocao
    {
        $grupoPromocao = GrupoPromocao::query()->create([
            'nome' => 'Grupo de teste',
            'slug' => 'grupo-de-teste-'.$jogo->id,
            'descricao' => 'Grupo promocional de teste.',
        ]);

        return Promocao::query()->create([
            'tipo' => 'preco_fixo',
            'percentual' => null,
            'data_fim' => $dataFim,
            'valor' => $valor,
            'jogo_id' => $jogo->id,
            'grupo_promocao_id' => $grupoPromocao->id,
        ]);
    }
}
