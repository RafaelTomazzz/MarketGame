<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrupoPromocaoSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = [
            [
                'nome' => 'Festival de Verão: Grandes Aventuras',
                'slug' => 'festival-de-verao-grandes-aventuras',
                'descricao' => 'Uma seleção de jogos para explorar, competir e aproveitar as férias de verão.',
            ],
            [
                'nome' => 'Operação Mira Certeira: Jogos de Tiro',
                'slug' => 'operacao-mira-certeira-jogos-de-tiro',
                'descricao' => 'Descontos especiais em jogos de tiro, ação intensa e combate cooperativo.',
            ],
        ];

        foreach ($grupos as $dados) {
            $existente = DB::table('grupo_promocoes')
                ->where('slug', $dados['slug'])
                ->exists();
            $atributos = [
                ...$dados,
                'deleted_at' => null,
                'updated_at' => now(),
            ];

            if ($existente) {
                DB::table('grupo_promocoes')
                    ->where('slug', $dados['slug'])
                    ->update($atributos);

                continue;
            }

            DB::table('grupo_promocoes')->insert([
                ...$atributos,
                'created_at' => now(),
            ]);
        }
    }
}
