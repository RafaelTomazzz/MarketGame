<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoPromocao extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'descricao',
        'promocao_id'
    ];

    public function promocoes(): HasMany
    {
        return $this->hasMany(Promocao::class);
    }

}
