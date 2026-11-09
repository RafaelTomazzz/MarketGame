<?php

namespace App\Http\Controllers;

use App\Models\Jogo;
use Illuminate\Contracts\View\View;

class JogoController extends Controller
{
    public function index(): View
    {
        $promocaoVigente = fn ($query) => $query->where('data_fim', '>=', now());

        $jogos = Jogo::query()
            ->with([
                'categorias',
                'promocao' => $promocaoVigente,
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('jogos.index', [
            'jogos' => $jogos,
        ]);
    }
}
