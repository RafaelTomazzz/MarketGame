<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UsuarioController extends Controller
{
    public function index(): View
    {
        $usuarios = User::query()
            ->select(['id', 'nome', 'email', 'cpf', 'telefone', 'created_at'])
            ->with('enderecos:id,user_id,uf,cep,cidade,bairro,rua,numero,complemento')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('usuarios.index', [
            'usuarios' => $usuarios,
        ]);
    }
}
