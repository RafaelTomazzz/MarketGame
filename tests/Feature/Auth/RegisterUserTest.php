<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_registration_creates_user_and_related_address(): void
    {
        $dados = $this->dadosCadastro();

        $response = $this->post(route('cadastro.store'), $dados);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Cadastro realizado com sucesso. Faça login para continuar.');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('enderecos', 1);

        $usuario = User::with('enderecos')->where('email', $dados['email'])->firstOrFail();
        $this->assertSame('12345678909', $usuario->cpf);
        $this->assertSame('11987654321', $usuario->telefone);
        $this->assertTrue(Hash::check($dados['password'], $usuario->password));
        $this->assertNotSame($usuario->cpf, DB::table('users')->where('id', $usuario->id)->value('cpf'));
        $this->assertSame('01234567', $usuario->enderecos->sole()->cep);
        $this->assertSame('SP', $usuario->enderecos->sole()->uf);
    }

    public function test_registration_rejects_invalid_cpf_without_creating_records(): void
    {
        $dados = $this->dadosCadastro(['cpf' => '12345678900']);

        $response = $this->from(route('cadastro'))->post(route('cadastro.store'), $dados);

        $response->assertRedirect(route('cadastro'));
        $response->assertSessionHasErrors(['cpf' => 'O CPF informado é inválido.']);
        $this->assertArrayNotHasKey('cpf', session()->getOldInput());
        $this->assertArrayNotHasKey('telefone', session()->getOldInput());
        $this->assertSame($dados['email'], session()->getOldInput('email'));
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('enderecos', 0);
    }

    public function test_registration_rejects_cpf_already_used_even_when_encrypted(): void
    {
        User::create([
            'nome' => 'Usuário Existente',
            'email' => 'existente@example.test',
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => 'senha-existente',
        ]);

        $dados = $this->dadosCadastro(['email' => 'novo@example.test']);

        $response = $this->from(route('cadastro'))->post(route('cadastro.store'), $dados);

        $response->assertRedirect(route('cadastro'));
        $response->assertSessionHasErrors(['cpf' => 'O CPF informado já está cadastrado.']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('enderecos', 0);
    }

    public function test_registration_requires_address_fields(): void
    {
        $dados = $this->dadosCadastro();
        unset($dados['rua']);

        $response = $this->from(route('cadastro'))->post(route('cadastro.store'), $dados);

        $response->assertRedirect(route('cadastro'));
        $response->assertSessionHasErrors(['rua']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('enderecos', 0);
    }

    #[DataProvider('invalidPasswords')]
    public function test_registration_rejects_passwords_that_do_not_meet_requirements(
        string $password,
        string $message,
    ): void {
        $dados = $this->dadosCadastro([
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response = $this->from(route('cadastro'))->post(route('cadastro.store'), $dados);

        $response->assertRedirect(route('cadastro'));
        $response->assertSessionHasErrors(['password' => $message]);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('enderecos', 0);
    }

    public static function invalidPasswords(): array
    {
        return [
            'fewer than eight characters' => [
                'Curta1A',
                'A senha deve ter pelo menos 8 caracteres.',
            ],
            'without uppercase letter' => [
                'senhasegura1',
                'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
            ],
            'without lowercase letter' => [
                'SENHASEGURA1',
                'A senha deve conter pelo menos uma letra maiúscula e uma minúscula.',
            ],
            'without number' => [
                'SenhaSegura',
                'A senha deve conter pelo menos um número.',
            ],
        ];
    }

    public function test_registration_form_displays_password_requirements(): void
    {
        $response = $this->get(route('cadastro'));

        $response->assertOk();
        $response->assertSeeText(
            'A senha deve ter pelo menos 8 caracteres, uma letra maiúscula, uma letra minúscula e um número.',
        );
        $response->assertSee('minlength="8"', false);
    }

    public function test_login_page_displays_registration_success_message(): void
    {
        $response = $this->withSession([
            'status' => 'Cadastro realizado com sucesso. Faça login para continuar.',
        ])->get(route('login'));

        $response->assertOk();
        $response->assertSeeText('Cadastro realizado com sucesso. Faça login para continuar.');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function dadosCadastro(array $overrides = []): array
    {
        return array_merge([
            'nome' => 'Usuário de Teste',
            'cpf' => '123.456.789-09',
            'telefone' => '(11) 98765-4321',
            'email' => 'teste@example.test',
            'password' => 'SenhaSegura1',
            'password_confirmation' => 'SenhaSegura1',
            'cep' => '01234-567',
            'uf' => 'sp',
            'cidade' => 'São Paulo',
            'bairro' => 'Centro',
            'rua' => 'Rua de Teste',
            'numero' => '100',
            'complemento' => null,
        ], $overrides);
    }
}
