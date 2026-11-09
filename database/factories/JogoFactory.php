<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Jogo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Jogo>
 */
class JogoFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Jogo $jogo): void {
            if ($jogo->categorias()->exists()) {
                return;
            }

            $categoria = Categoria::query()->first();

            if (! $categoria) {
                $slug = fake('pt_BR')->unique()->slug();
                $categoria = Categoria::query()->create([
                    'nome' => Str::headline($slug),
                    'slug' => $slug,
                    'icon' => 'bi bi-controller',
                ]);
            }

            $jogo->categorias()->attach($categoria);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = fake('pt_BR');
        $titulo = $faker->unique()->words(3, true);

        return [
            'nome' => $titulo,
            'slug' => Str::slug($titulo.'-'.$faker->unique()->numerify('####')),
            'descricao' => $faker->paragraph(),
            'preco' => $faker->randomFloat(2, 49.90, 349.90),
            'imagem' => null,
            'desenvolvedor' => $faker->company(),
            'data_lancamento' => $faker->dateTimeBetween('-15 years', 'now'),
        ];
    }
}
