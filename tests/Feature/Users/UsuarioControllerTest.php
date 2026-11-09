<?php

namespace Tests\Feature\Users;

use App\Models\Endereco;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_user_cards_with_personal_details(): void
    {
        $dataCadastro = now()->setDate(2026, 3, 4)->setTime(11, 27);
        $usuario = $this->criarUsuario('Usuário de Teste', 'teste@example.test', '12345678909');
        $usuario->forceFill([
            'created_at' => $dataCadastro,
            'updated_at' => $dataCadastro,
        ])->save();
        Endereco::create([
            'user_id' => $usuario->id,
            'uf' => 'SP',
            'cep' => '01234567',
            'cidade' => 'São Paulo',
            'bairro' => 'Centro',
            'rua' => 'Rua de Teste',
            'numero' => '100',
            'complemento' => 'Apto 2',
        ]);

        $response = $this->get(route('usuarios.index'));

        $response->assertOk();
        $response->assertViewIs('usuarios.index');
        $response->assertSeeText($usuario->nome);
        $response->assertSeeText($usuario->email);
        $response->assertSeeText($usuario->cpf);
        $response->assertSeeText($usuario->telefone);
        $response->assertSeeText('04/03/2026 11:27');
        $response->assertSeeText('Rua de Teste, 100 - Apto 2');
        $response->assertSeeText('Centro, São Paulo - SP');
        $response->assertSeeText('CEP: 01234567');
    }

    public function test_guest_can_navigate_to_the_remaining_user_cards(): void
    {
        $usuarioAntigo = $this->criarUsuario('Acesso de Teste', 'acesso@example.test', '00000000001');
        $dataAntiga = now()->setDate(2026, 1, 1)->startOfDay();
        $usuarioAntigo->forceFill([
            'created_at' => $dataAntiga,
            'updated_at' => $dataAntiga,
        ])->save();

        for ($i = 1; $i <= 15; $i++) {
            $sufixo = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $cpf = str_pad((string) ($i + 1), 11, '0', STR_PAD_LEFT);

            $usuario = $this->criarUsuario(
                "Usuário {$sufixo}",
                "usuario{$sufixo}@example.test",
                $cpf,
            );
            $dataCadastro = now()->setDate(2026, 1, $i + 1)->startOfDay();
            $usuario->forceFill([
                'created_at' => $dataCadastro,
                'updated_at' => $dataCadastro,
            ])->save();
        }

        $primeiraPagina = $this->get(route('usuarios.index'));

        $primeiraPagina->assertOk();
        $primeiraPagina->assertViewHas(
            'usuarios',
            fn ($usuarios): bool => $usuarios->first()->nome === 'Usuário 15',
        );

        $response = $this->get(route('usuarios.index', ['page' => 2]));

        $response->assertOk();
        $response->assertSeeText('Acesso de Teste');
        $response->assertDontSee('Usuário 01');
    }

    private function criarUsuario(string $nome, string $email, string $cpf): User
    {
        return User::create([
            'nome' => $nome,
            'email' => $email,
            'cpf' => $cpf,
            'telefone' => '11999990000',
            'password' => 'senha-segura',
        ]);
    }
}
