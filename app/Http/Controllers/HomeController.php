<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
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

        $slugCategoria = request()->query('categoria');
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
            ->limit(8)
            ->get();

        return view('home', [
            'categorias' => $categorias,
            'categoriaSelecionada' => $categoriaSelecionada,
            'jogosDaCategoria' => $jogosDaCategoria,
            'jogosEmDestaque' => $jogosEmDestaque,
            'jogosEmPromocao' => $jogosEmPromocao,
        ]);
    }
}
