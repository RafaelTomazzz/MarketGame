<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'slug',
        'icon',
    ];

    public function jogos(): BelongsToMany
    {
        return $this->belongsToMany(Jogo::class, 'jogo_categorias', 'categoria_id', 'jogo_id');
    }
}
