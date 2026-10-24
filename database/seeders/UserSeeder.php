<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = fake('pt_BR');
        $faker->seed(1234);
        $cpfs = [];
        $ddds = [11, 21, 31, 41, 51, 61, 71, 81, 85, 62];

        for ($index = 1; $index <= 50; $index++) {
            $nome = $index === 1
                ? 'Conta Demo MarketGame'
                : $faker->name();
            // Conta demo: demo@seed.marketgame.example.com / password
            $email = $index === 1
                ? 'demo@seed.marketgame.example.com'
                : Str::slug($nome, '.').sprintf('%02d', $index).'@seed.marketgame.example.com';

            do {
                $baseCpf = $faker->numerify('#########');
            } while (preg_match('/^(\d)\1{8}$/', $baseCpf) === 1);

            $cpf = $this->gerarCpf($baseCpf);
            while (isset($cpfs[$cpf])) {
                do {
                    $baseCpf = $faker->numerify('#########');
                } while (preg_match('/^(\d)\1{8}$/', $baseCpf) === 1);

                $cpf = $this->gerarCpf($baseCpf);
            }

            $cpfs[$cpf] = true;

            User::updateOrCreate(
                ['email' => $email],
                [
                    'nome' => $nome,
                    'cpf' => $cpf,
                    'telefone' => sprintf(
                        '(%02d) 9%04d-%04d',
                        $faker->randomElement($ddds),
                        $faker->numberBetween(1000, 9999),
                        $faker->numberBetween(1000, 9999),
                    ),
                    'password' => 'password',
                ],
            );
        }
    }

    private function gerarCpf(string $base): string
    {
        $digits = str_split($base);

        $sum = 0;
        for ($index = 0; $index < 9; $index++) {
            $sum += (int) $digits[$index] * (10 - $index);
        }
        $firstCheckDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        $digits[] = (string) $firstCheckDigit;

        $sum = 0;
        for ($index = 0; $index < 10; $index++) {
            $sum += (int) $digits[$index] * (11 - $index);
        }
        $secondCheckDigit = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        $digits[] = (string) $secondCheckDigit;

        $cpf = implode('', $digits);

        return substr($cpf, 0, 3).'.'.substr($cpf, 3, 3).'.'.substr($cpf, 6, 3).'-'.substr($cpf, 9, 2);
    }
}
