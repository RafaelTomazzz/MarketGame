<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jogo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'preco',
        'imagem',
        'desenvolvedor',
        'data_lancamento',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'data_lancamento' => 'datetime',
        ];
    }

    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_jogos', 'jogo_id', 'pedido_id')
            ->withPivot('quantidade')
            ->withTimestamps();
    }

    public function usuariosComJogoNoCarrinho(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'jogo_user', 'jogo_id', 'user_id')
            ->withTimestamps();
    }

    public function promocao(): HasOne
    {
        return $this->hasOne(Promocao::class, 'jogo_id');
    }

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'jogo_categorias', 'jogo_id', 'categoria_id');
    }
}
