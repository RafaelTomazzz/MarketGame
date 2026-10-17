<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\TipoPromocao;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promocao extends Model
{
    protected $fillable = [
        'tipo',
        'percentual',
        'data_fim',
        'valor',
        'jogo_id'
    ];

    protected $casts = [
        'tipo' => TipoPromocao::class,
    ];

    public function jogo() : BelongsTo
    {
        return $this->belongsTo(Jogo::class);
    }
}
