<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categoria extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'icon'
    ];

    public function jogos() : BelongsToMany
    {
        return $this->belongsToMany(Jogo::class, 'jogo_categorias', 'categoria_id', 'jogo_id');
    }
}
