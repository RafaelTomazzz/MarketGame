<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\GrupoPromocao;
use App\Models\Jogo;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $promocaoVigente = fn ($query) => $query->where('data_fim', '>=', now());

        $jogos = Jogo::query()->with([
            'categorias',
            'promocao' => $promocaoVigente,
        ]);

        $categorias = Categoria::query()
            ->orderBy('nome')
            ->get();

        $slugCategoria = request()->query('categoria', 'acao');
        $categoriaSelecionada = is_string($slugCategoria)
            ? $categorias->firstWhere('slug', $slugCategoria)
            : null;

        $jogosDaCategoria = $categoriaSelecionada
            ? $categoriaSelecionada->jogos()
                ->with([
                    'categorias',
                    'promocao' => $promocaoVigente,
                ])
                ->orderBy('nome')
                ->get()
            : collect();

        $jogosEmDestaque = (clone $jogos)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $jogosEmPromocao = (clone $jogos)
            ->whereHas('promocao', $promocaoVigente)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $gruposEmPromocao = GrupoPromocao::query()
            ->whereHas('promocoes', $promocaoVigente)
            ->with([
                'promocoes' => fn ($query) => $query
                    ->where('data_fim', '>=', now())
                    ->orderBy('id')
                    ->with([
                        'jogo' => fn ($query) => $query->with([
                            'categorias',
                            'promocao' => $promocaoVigente,
                        ]),
                    ]),
            ])
            ->orderBy('nome')
            ->get();

        return view('home', [
            'categorias' => $categorias,
            'categoriaSelecionada' => $categoriaSelecionada,
            'jogosDaCategoria' => $jogosDaCategoria,
            'jogosEmDestaque' => $jogosEmDestaque,
            'jogosEmPromocao' => $jogosEmPromocao,
            'gruposEmPromocao' => $gruposEmPromocao,
        ]);
    }
}
