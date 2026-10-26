<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoPromocao extends Model
{
    use SoftDeletes;

    protected $table = 'grupo_promocoes';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
    ];

    public function promocoes(): HasMany
    {
        return $this->hasMany(Promocao::class);
    }
}
