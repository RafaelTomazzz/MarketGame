<?php

namespace Tests\Feature\Pedido;

use App\Models\Categoria;
use App\Models\GrupoPromocao;
use App\Models\Jogo;
use App\Models\Promocao;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PedidoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_creates_pending_order_and_clears_the_cart(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $user = $this->criarUsuario();
        $formaPagamento = $user->formasPagamento()->create([
            'nome' => 'Pix',
            'tipo' => 'pix',
        ]);
        $jogoPromocional = $this->criarJogo('jogo-promocional', '59.90');
        $jogoRegular = $this->criarJogo('jogo-regular', '10.00');
        $this->criarPromocao($jogoPromocional, '39.90', '2026-10-27 12:00:00');
        $user->jogosNoCarrinho()->attach($jogoPromocional->id);
        $user->jogosNoCarrinho()->attach($jogoPromocional->id);
        $user->jogosNoCarrinho()->attach($jogoRegular->id);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'forma_pagamento_id' => $formaPagamento->id,
        ]);

        $pedido = $user->pedidos()->firstOrFail();
        $jogosDoPedido = $pedido->jogos()->orderBy('jogo_id')->get();

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('order_confirmation', [
            'id' => $pedido->id,
            'total' => '94.80',
        ]);
        $this->assertSame('pendente', $pedido->status);
        $this->assertSame('94.80', $pedido->total);
        $this->assertSame('5.00', $pedido->frete);
        $this->assertCount(2, $jogosDoPedido);
        $this->assertSame(2, $jogosDoPedido[0]->pivot->quantidade);
        $this->assertSame(5990, (int) round((float) $jogosDoPedido[0]->pivot->preco_original * 100));
        $this->assertSame(3990, (int) round((float) $jogosDoPedido[0]->pivot->preco_unitario * 100));
        $this->assertSame(1, $jogosDoPedido[1]->pivot->quantidade);
        $this->assertSame(1000, (int) round((float) $jogosDoPedido[1]->pivot->preco_original * 100));
        $this->assertSame(1000, (int) round((float) $jogosDoPedido[1]->pivot->preco_unitario * 100));
        $this->assertDatabaseHas('pagamentos', [
            'pedido_id' => $pedido->id,
            'forma_pagamento_id' => $formaPagamento->id,
            'valor' => '94.80',
        ]);
        $this->assertDatabaseMissing('jogo_user', ['user_id' => $user->id]);
    }

    public function test_guest_is_redirected_to_login_without_creating_an_order(): void
    {
        $response = $this->post(route('orders.store'), ['forma_pagamento_id' => 1]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_user_cannot_create_an_order_with_another_users_payment_method(): void
    {
        $user = $this->criarUsuario();
        $outroUsuario = $this->criarUsuario('outro-pagamento@example.test');
        $metodoPagamentoAlheio = $outroUsuario->formasPagamento()->create([
            'nome' => 'Pix alheio',
            'tipo' => 'pix',
        ]);
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'forma_pagamento_id' => $metodoPagamentoAlheio->id,
        ]);

        $response->assertSessionHasErrors('forma_pagamento_id');
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseHas('jogo_user', ['user_id' => $user->id, 'jogo_id' => $jogo->id]);
    }

    public function test_order_is_not_created_when_the_cart_is_empty(): void
    {
        $user = $this->criarUsuario();
        $formaPagamento = $user->formasPagamento()->create([
            'nome' => 'Pix',
            'tipo' => 'pix',
        ]);

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'forma_pagamento_id' => $formaPagamento->id,
        ]);

        $response->assertSessionHasErrors([
            'carrinho' => 'Adicione ao menos um jogo antes de finalizar o pedido.',
        ]);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pagamentos', 0);
    }

    public function test_order_is_not_created_when_a_cart_game_is_unavailable(): void
    {
        $user = $this->criarUsuario();
        $formaPagamento = $user->formasPagamento()->create([
            'nome' => 'Pix',
            'tipo' => 'pix',
        ]);
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);
        $jogo->delete();

        $response = $this->actingAs($user)->post(route('orders.store'), [
            'forma_pagamento_id' => $formaPagamento->id,
        ]);

        $response->assertSessionHasErrors([
            'carrinho' => 'Um ou mais jogos não estão mais disponíveis. Revise seu carrinho antes de finalizar.',
        ]);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseHas('jogo_user', ['user_id' => $user->id, 'jogo_id' => $jogo->id]);
    }

    public function test_payment_method_is_required_to_create_an_order(): void
    {
        $user = $this->criarUsuario();
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);

        $response = $this->actingAs($user)->post(route('orders.store'), []);

        $response->assertSessionHasErrors('forma_pagamento_id');
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseHas('jogo_user', ['user_id' => $user->id, 'jogo_id' => $jogo->id]);
    }

    public function test_cart_page_displays_the_checkout_form_and_total_with_shipping(): void
    {
        $user = $this->criarUsuario();
        $formaPagamento = $user->formasPagamento()->create([
            'nome' => 'Pix',
            'tipo' => 'pix',
        ]);
        $jogo = $this->criarJogo();
        $user->jogosNoCarrinho()->attach($jogo->id);

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertOk();
        $response->assertSee(route('orders.store'), false);
        $response->assertSee('form="checkout-form" required', false);
        $response->assertSee('R$ 5,00');
        $response->assertSee('R$ 64,90');
        $response->assertSee('id="checkout-form"', false);
        $response->assertDontSee('disabled', false);
        $response->assertViewHas('formasPagamento', fn ($formasPagamento): bool => $formasPagamento->contains($formaPagamento));
    }

    public function test_user_orders_modal_displays_only_their_orders_with_items_and_payment_details(): void
    {
        $user = $this->criarUsuario();
        $outroUsuario = $this->criarUsuario('outro-pedido@example.test');
        $metodoPagamento = $user->formasPagamento()->create([
            'nome' => 'Cartão de crédito final 4242',
            'tipo' => 'credito',
        ]);
        $outroMetodoPagamento = $outroUsuario->formasPagamento()->create([
            'nome' => 'Pix particular',
            'tipo' => 'pix',
        ]);
        $jogo = $this->criarJogo('Jogo comprado', '59.90');
        $jogoAlheio = $this->criarJogo('Jogo de outro usuário', '19.90');
        $pedido = $user->pedidos()->create([
            'status' => 'pendente',
            'total' => '64.90',
            'frete' => '5.00',
        ]);
        $pedido->jogos()->attach($jogo->id, [
            'quantidade' => 1,
            'preco_original' => '59.90',
            'preco_unitario' => '59.90',
        ]);
        $pedido->pagamentos()->create([
            'forma_pagamento_id' => $metodoPagamento->id,
            'valor' => '64.90',
        ]);
        $segundoPedido = $user->pedidos()->create([
            'status' => 'aprovado',
            'total' => '24.90',
            'frete' => '5.00',
        ]);
        $segundoPedido->jogos()->attach($jogo->id, [
            'quantidade' => 1,
            'preco_original' => '19.90',
            'preco_unitario' => '19.90',
        ]);
        $segundoPedido->pagamentos()->create([
            'forma_pagamento_id' => $metodoPagamento->id,
            'valor' => '24.90',
        ]);
        $pedidoAlheio = $outroUsuario->pedidos()->create([
            'status' => 'aprovado',
            'total' => '24.90',
            'frete' => '5.00',
        ]);
        $pedidoAlheio->jogos()->attach($jogoAlheio->id, [
            'quantidade' => 1,
            'preco_original' => '19.90',
            'preco_unitario' => '19.90',
        ]);
        $pedidoAlheio->pagamentos()->create([
            'forma_pagamento_id' => $outroMetodoPagamento->id,
            'valor' => '24.90',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('data-bs-target="#user-orders-modal"', false);
        $response->assertSee('id="user-orders-modal"', false);
        $response->assertSee('Pedido #'.$pedido->id);
        $response->assertSee('Pedido #'.$segundoPedido->id);
        $response->assertSee('Jogo comprado');
        $response->assertSee('Cartão de crédito final 4242');
        $response->assertSee('Aprovado');
        $response->assertSee('R$ 64,90');
        $response->assertSee('R$ 5,00');
        $response->assertDontSee('Pedido #'.$pedidoAlheio->id);
        $response->assertDontSee('Jogo de outro usuário');
        $response->assertDontSee('Pix particular');
    }

    public function test_orders_modal_shows_an_empty_state_for_a_user_without_orders(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('id="user-orders-modal"', false);
        $response->assertSee('Você ainda não fez pedidos');
    }

    public function test_guest_page_does_not_render_the_user_orders_modal(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertDontSee('id="user-orders-modal"', false);
    }

    private function criarUsuario(string $email = 'pedido@example.test'): User
    {
        return User::query()->create([
            'nome' => 'Usuário de pedido',
            'email' => $email,
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => 'senha-segura',
        ]);
    }

    private function criarJogo(string $slug = 'jogo-do-pedido', string $preco = '59.90'): Jogo
    {
        $jogo = Jogo::query()->create([
            'nome' => $slug,
            'slug' => $slug,
            'descricao' => 'Descrição do jogo de pedido.',
            'preco' => $preco,
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
        ]);
        $categoria = Categoria::query()->create([
            'nome' => 'Categoria de pedido',
            'slug' => 'categoria-de-pedido-'.$jogo->id,
            'icon' => 'bi-controller',
        ]);
        $jogo->categorias()->attach($categoria->id);

        return $jogo;
    }

    private function criarPromocao(Jogo $jogo, string $valor, string $dataFim): Promocao
    {
        $grupoPromocao = GrupoPromocao::query()->create([
            'nome' => 'Grupo de pedido',
            'slug' => 'grupo-de-pedido-'.$jogo->id,
            'descricao' => 'Promoção usada no teste de pedido.',
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
