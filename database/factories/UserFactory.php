<?php

namespace Database\Factories;

use App\Models\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('pt_BR');
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $cpf = $this->cpfFicticio($faker);
        $ddds = [11, 21, 31, 41, 51, 61, 71, 81, 85, 62];

        return [
            'nome' => mb_substr("{$firstName} {$lastName}", 0, 20),
            'email' => Str::slug("{$firstName}.{$lastName}").$faker->unique()->numerify('########').'@example.test',
            'cpf' => $cpf,
            'telefone' => sprintf(
                '%02d9%08d',
                Arr::random($ddds),
                $faker->numberBetween(0, 99999999),
            ),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            if ($user->enderecos()->exists()) {
                return;
            }

            $faker = fake('pt_BR');
            $localidades = [
                ['uf' => 'SP', 'cidade' => 'São Paulo', 'bairro' => 'Sé', 'cep' => '01001-000'],
                ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro', 'bairro' => 'Centro', 'cep' => '20010-000'],
                ['uf' => 'MG', 'cidade' => 'Belo Horizonte', 'bairro' => 'Centro', 'cep' => '30130-000'],
                ['uf' => 'PR', 'cidade' => 'Curitiba', 'bairro' => 'Centro', 'cep' => '80010-000'],
                ['uf' => 'RS', 'cidade' => 'Porto Alegre', 'bairro' => 'Centro Histórico', 'cep' => '90010-150'],
                ['uf' => 'BA', 'cidade' => 'Salvador', 'bairro' => 'Comércio', 'cep' => '40020-000'],
                ['uf' => 'PE', 'cidade' => 'Recife', 'bairro' => 'Santo Antônio', 'cep' => '50010-000'],
                ['uf' => 'CE', 'cidade' => 'Fortaleza', 'bairro' => 'Centro', 'cep' => '60060-000'],
                ['uf' => 'DF', 'cidade' => 'Brasília', 'bairro' => 'Asa Sul', 'cep' => '70040-010'],
                ['uf' => 'GO', 'cidade' => 'Goiânia', 'bairro' => 'Setor Central', 'cep' => '74003-010'],
            ];
            $localidade = $faker->randomElement($localidades);

            $user->enderecos()->create([
                ...$localidade,
                'rua' => $faker->streetName(),
                'numero' => (string) $faker->buildingNumber(),
                'complemento' => $faker->optional(0.3)->secondaryAddress(),
            ]);
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    private function cpfFicticio(Generator $faker): string
    {
        do {
            $cpf = $faker->unique()->cpf(false);
        } while (preg_match('/^(\d)\1{10}$/', $cpf) === 1);

        return vsprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', str_split($cpf));
    }
}
