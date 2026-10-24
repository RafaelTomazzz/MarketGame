<?php

namespace Database\Seeders;

use App\Enums\TipoPromocao;
use App\Models\Jogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PromocaoSeeder extends Seeder
{
    public function run(): void
    {
        $promocoes = [
            'festival-de-verao-grandes-aventuras' => [
                'data_fim' => '2027-02-28 23:59:59',
                'jogos' => [
                    'forza-horizon-5' => 25,
                    'sea-of-thieves' => 20,
                    'no-mans-sky' => 30,
                    'hogwarts-legacy' => 25,
                    'stardew-valley' => 20,
                    'terraria' => 20,
                    'it-takes-two' => 25,
                    'satisfactory' => 20,
                    'palworld' => 25,
                    'elden-ring' => 20,
                ],
            ],
            'operacao-mira-certeira-jogos-de-tiro' => [
                'data_fim' => '2026-12-31 23:59:59',
                'jogos' => [
                    'call-of-duty-modern-warfare-4' => 20,
                    'doom-the-dark-ages' => 25,
                    'helldivers-2' => 20,
                    'borderlands-4' => 20,
                    'cyberpunk-2077' => 25,
                    'grand-theft-auto-v-enhanced' => 20,
                    'resident-evil-4' => 25,
                    'resident-evil-village' => 25,
                    'red-dead-redemption-2' => 20,
                    'the-last-of-us-part-i' => 20,
                ],
            ],
        ];

        $grupos = DB::table('grupo_promocoes')
            ->whereIn('slug', array_keys($promocoes))
            ->pluck('id', 'slug');
        $slugsJogos = collect($promocoes)->flatMap(
            fn (array $grupo) => array_keys($grupo['jogos']),
        );
        $jogos = Jogo::query()->whereIn('slug', $slugsJogos)->get()->keyBy('slug');

        foreach ($promocoes as $slugGrupo => $dadosGrupo) {
            $grupo = $grupos->get($slugGrupo);

            if ($grupo === null) {
                throw new RuntimeException("Grupo promocional não encontrado: {$slugGrupo}");
            }

            foreach ($dadosGrupo['jogos'] as $slugJogo => $percentual) {
                $jogo = $jogos->get($slugJogo);

                if ($jogo === null) {
                    throw new RuntimeException("Jogo não encontrado para a promoção: {$slugJogo}");
                }

                $existe = DB::table('promocoes')
                    ->where('jogo_id', $jogo->id)
                    ->exists();
                $atributos = [
                    'grupo_promocao_id' => $grupo,
                    'tipo' => TipoPromocao::Percentual->value,
                    'percentual' => $percentual,
                    'data_fim' => $dadosGrupo['data_fim'],
                    'valor' => $this->calcularPrecoPromocional($jogo->preco, $percentual),
                    'deleted_at' => null,
                    'updated_at' => now(),
                ];

                if ($existe) {
                    DB::table('promocoes')
                        ->where('jogo_id', $jogo->id)
                        ->update($atributos);

                    continue;
                }

                DB::table('promocoes')->insert([
                    ...$atributos,
                    'jogo_id' => $jogo->id,
                    'created_at' => now(),
                ]);
            }
        }
    }

    private function calcularPrecoPromocional(string $preco, int $percentual): string
    {
        [$reais, $centavos] = array_pad(explode('.', $preco, 2), 2, '00');
        $precoEmCentavos = ((int) $reais * 100) + (int) str_pad($centavos, 2, '0');
        $valorPromocionalEmCentavos = intdiv($precoEmCentavos * (100 - $percentual) + 50, 100);

        return sprintf('%d.%02d', intdiv($valorPromocionalEmCentavos, 100), $valorPromocionalEmCentavos % 100);
    }
}
