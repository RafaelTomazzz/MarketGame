<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class EnderecoSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('pt_BR');
        $faker->seed(5678);
        $localidades = [
            ['uf' => 'SP', 'cidade' => 'São Paulo', 'bairro' => 'Sé', 'cep' => '01001-000'],
            ['uf' => 'SP', 'cidade' => 'São Paulo', 'bairro' => 'Bela Vista', 'cep' => '01310-100'],
            ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro', 'bairro' => 'Centro', 'cep' => '20010-000'],
            ['uf' => 'RJ', 'cidade' => 'Rio de Janeiro', 'bairro' => 'Copacabana', 'cep' => '22041-001'],
            ['uf' => 'MG', 'cidade' => 'Belo Horizonte', 'bairro' => 'Centro', 'cep' => '30130-000'],
            ['uf' => 'PR', 'cidade' => 'Curitiba', 'bairro' => 'Centro', 'cep' => '80010-000'],
            ['uf' => 'RS', 'cidade' => 'Porto Alegre', 'bairro' => 'Centro Histórico', 'cep' => '90010-150'],
            ['uf' => 'BA', 'cidade' => 'Salvador', 'bairro' => 'Comércio', 'cep' => '40020-000'],
            ['uf' => 'PE', 'cidade' => 'Recife', 'bairro' => 'Santo Antônio', 'cep' => '50010-000'],
            ['uf' => 'CE', 'cidade' => 'Fortaleza', 'bairro' => 'Centro', 'cep' => '60060-000'],
            ['uf' => 'DF', 'cidade' => 'Brasília', 'bairro' => 'Asa Sul', 'cep' => '70040-010'],
            ['uf' => 'GO', 'cidade' => 'Goiânia', 'bairro' => 'Setor Central', 'cep' => '74003-010'],
        ];

        User::query()
            ->where('email', 'like', '%@seed.marketgame.example.com')
            ->each(function (User $usuario) use ($faker, $localidades): void {
                $localidade = $faker->randomElement($localidades);

                $usuario->enderecos()->updateOrCreate([], [
                    ...$localidade,
                    'rua' => $faker->streetName(),
                    'numero' => (string) $faker->buildingNumber(),
                    'complemento' => $faker->optional(0.3)->secondaryAddress(),
                ]);
            });
    }
}
