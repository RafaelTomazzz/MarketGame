<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nome' => 'Ação', 'slug' => 'acao', 'icon' => 'bi bi-lightning-charge-fill'],
            ['nome' => 'Aventura', 'slug' => 'aventura', 'icon' => 'bi bi-compass-fill'],
            ['nome' => 'RPG', 'slug' => 'rpg', 'icon' => 'bi bi-shield-fill'],
            ['nome' => 'Estratégia', 'slug' => 'estrategia', 'icon' => 'bi bi-diagram-3-fill'],
            ['nome' => 'Simulação', 'slug' => 'simulacao', 'icon' => 'bi bi-sliders'],
            ['nome' => 'Esportes', 'slug' => 'esportes', 'icon' => 'bi bi-trophy-fill'],
            ['nome' => 'Corrida', 'slug' => 'corrida', 'icon' => 'bi bi-car-front-fill'],
            ['nome' => 'Terror', 'slug' => 'terror', 'icon' => 'bi bi-emoji-dizzy-fill'],
            ['nome' => 'Plataforma', 'slug' => 'plataforma', 'icon' => 'bi bi-joystick'],
            ['nome' => 'Indie', 'slug' => 'indie', 'icon' => 'bi bi-stars'],
            ['nome' => 'Sobrevivência', 'slug' => 'sobrevivencia', 'icon' => 'bi bi-heart-pulse-fill'],
            ['nome' => 'Multijogador', 'slug' => 'multijogador', 'icon' => 'bi bi-people-fill'],
        ];

        foreach ($categorias as $dados) {
            $categoria = Categoria::withTrashed()->updateOrCreate(
                ['slug' => $dados['slug']],
                $dados,
            );

            if ($categoria->trashed()) {
                $categoria->restore();
            }
        }
    }
}
