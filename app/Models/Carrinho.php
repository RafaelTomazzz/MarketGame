<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Jogo;

class Carrinho extends Model
{
    protected $table = 'carrinhos';

    protected $fillable = [];

    public function itens(): HasMany
    {
        return $this->hasMany(ItemCarrinho::class, 'carrinho_id');
    }

    public function jogos(): BelongsToMany
    {
        return $this->belongsToMany(Jogo::class, 'item_carrinhos', 'carrinho_id', 'jogo_id')
                    ->withPivot('quantidade')
                    ->withTimestamps();
    }
}
