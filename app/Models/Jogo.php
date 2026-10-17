<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jogo extends Model
{
    protected $table = 'jogos';

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'categoria_id',
        'imagem',
    ];

    public function pedidos() : BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_jogos', 'jogo_id', 'pedido_id');
    }

    public function carrinhos() : BelongsToMany
    {
        return $this->belongsToMany(Carrinho::class, 'item_carrinhos', 'jogo_id', 'carrinho_id');
    }

    public function promocao() : BelongsTo
    {
        return $this->belongsTo(Promocao::class, 'promocao_id');
    }

    public function categoria() : BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'jogo_categorias', 'jogo_id', 'categoria_id');
    }

}
