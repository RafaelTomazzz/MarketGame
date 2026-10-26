<?php

namespace Tests\Feature\Cart;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FormaPagamentoControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_creates_a_pix_method_for_their_account(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('payment-methods.store'), ['tipo' => 'pix']);

        $formaPagamento = $user->formasPagamento()->firstOrFail();

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success', 'Método de pagamento cadastrado com sucesso.');
        $response->assertSessionHas('selected_payment_method_id', $formaPagamento->id);
        $this->assertSame('Pix', $formaPagamento->nome);
        $this->assertSame('pix', $formaPagamento->tipo);
        $this->assertNull($formaPagamento->ultimos_digitos);
        $this->assertDatabaseHas('forma_pagamentos', ['id' => $formaPagamento->id, 'user_id' => $user->id]);
    }

    public function test_authenticated_user_saves_only_card_metadata_and_discards_the_full_number(): void
    {
        $user = $this->criarUsuario();
        $this->travelTo('2026-10-26 12:00:00');

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('payment-methods.store'), [
                'tipo' => 'credito',
                'numero_cartao' => '4111 1111 1111 1111',
                'titular_cartao' => 'Pessoa Titular',
                'bandeira' => 'Visa',
                'validade' => '2028-04',
            ]);

        $formaPagamento = $user->formasPagamento()->firstOrFail();

        $response->assertRedirect(route('cart.index'));
        $this->assertSame('Cartão de crédito final 1111', $formaPagamento->nome);
        $this->assertSame('credito', $formaPagamento->tipo);
        $this->assertSame('Pessoa Titular', $formaPagamento->titular);
        $this->assertSame('Visa', $formaPagamento->bandeira);
        $this->assertSame('1111', $formaPagamento->ultimos_digitos);
        $this->assertSame('2028-04', $formaPagamento->validade->format('Y-m'));
        $this->assertNotSame('Pessoa Titular', $formaPagamento->getRawOriginal('titular'));
        $this->assertStringNotContainsString(
            '4111111111111111',
            json_encode($formaPagamento->getAttributes(), JSON_THROW_ON_ERROR),
        );
    }

    public function test_card_details_are_required_for_debit_and_credit_methods(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->post(route('payment-methods.store'), [
            'tipo' => 'debito',
        ]);

        $response->assertSessionHasErrors([
            'numero_cartao',
            'titular_cartao',
            'bandeira',
            'validade',
        ]);
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    public function test_payment_type_must_be_one_of_the_available_options(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->post(route('payment-methods.store'), [
            'tipo' => 'boleto',
        ]);

        $response->assertSessionHasErrors('tipo');
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    public function test_invalid_card_number_is_rejected_and_not_flashed_back_to_the_session(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('payment-methods.store'), [
                'tipo' => 'credito',
                'numero_cartao' => '4111111111111112',
                'titular_cartao' => 'Pessoa Titular',
                'bandeira' => 'Visa',
                'validade' => now()->addYear()->format('Y-m'),
            ]);

        $response->assertSessionHasErrors('numero_cartao');
        $response->assertSessionHas('_old_input', function (array $input): bool {
            return ($input['numero_cartao'] ?? null) === null;
        });
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    public function test_expired_card_is_rejected(): void
    {
        $this->travelTo('2026-10-26 12:00:00');
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->post(route('payment-methods.store'), [
            'tipo' => 'debito',
            'numero_cartao' => '4111111111111111',
            'titular_cartao' => 'Pessoa Titular',
            'bandeira' => 'Visa',
            'validade' => '2026-09',
        ]);

        $response->assertSessionHasErrors([
            'validade' => 'A validade do cartão deve estar no mês atual ou no futuro.',
        ]);
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    public function test_cvv_is_never_accepted_or_stored(): void
    {
        $user = $this->criarUsuario();

        $response = $this->actingAs($user)->post(route('payment-methods.store'), [
            'tipo' => 'credito',
            'numero_cartao' => '4111111111111111',
            'titular_cartao' => 'Pessoa Titular',
            'bandeira' => 'Visa',
            'validade' => now()->addYear()->format('Y-m'),
            'cvv' => '123',
        ]);

        $response->assertSessionHasErrors('cvv');
        $response->assertSessionHas('_old_input', function (array $input): bool {
            return ($input['cvv'] ?? null) === null
                && ($input['numero_cartao'] ?? null) === null;
        });
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    public function test_guest_is_redirected_to_login_without_creating_a_payment_method(): void
    {
        $response = $this->post(route('payment-methods.store'), ['tipo' => 'pix']);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('forma_pagamentos', 0);
    }

    private function criarUsuario(): User
    {
        return User::query()->create([
            'nome' => 'Usuário de pagamento',
            'email' => 'pagamento@example.test',
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => 'senha-segura',
        ]);
    }
}
