<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Jogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use RuntimeException;

class JogoSeeder extends Seeder
{
    public function run(): void
    {
        $dados = json_decode(
            File::get(database_path('data/jogos.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $jogos = $dados['jogos'];

        $categorias = Categoria::query()
            ->whereIn('slug', collect($jogos)->flatMap(fn (array $jogo) => $jogo['categorias'])->unique())
            ->pluck('id', 'slug');

        foreach ($jogos as $dadosJogo) {
            $slugsCategorias = $dadosJogo['categorias'];
            $categoriasAusentes = array_diff($slugsCategorias, $categorias->keys()->all());

            if ($slugsCategorias === [] || $categoriasAusentes !== []) {
                throw new RuntimeException(
                    "Categorias ausentes para o jogo [{$dadosJogo['slug']}]: ".implode(', ', $categoriasAusentes),
                );
            }

            $jogo = Jogo::withTrashed()->updateOrCreate(
                ['slug' => $dadosJogo['slug']],
                [
                    'nome' => $dadosJogo['nome'],
                    'descricao' => $dadosJogo['descricao'],
                    'preco' => $dadosJogo['preco'],
                    'imagem' => $dadosJogo['imagem'],
                    'desenvolvedor' => $dadosJogo['desenvolvedor'],
                    'data_lancamento' => $dadosJogo['data_lancamento'],
                ],
            );

            if ($jogo->trashed()) {
                $jogo->restore();
            }

            $jogo->categorias()->sync(
                collect($slugsCategorias)->map(fn (string $slug) => $categorias[$slug])->all(),
            );
        }
    }
}
