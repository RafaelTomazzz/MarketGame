<?php

namespace App\Models;

use App\Enums\TipoPromocao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promocao extends Model
{
    use SoftDeletes;

    protected $table = 'promocoes';

    protected $fillable = [
        'tipo',
        'percentual',
        'data_fim',
        'valor',
        'jogo_id',
        'grupo_promocao_id',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoPromocao::class,
            'percentual' => 'integer',
            'data_fim' => 'datetime',
            'valor' => 'decimal:2',
        ];
    }

    public function jogo(): BelongsTo
    {
        return $this->belongsTo(Jogo::class);
    }

    public function grupoPromocao(): BelongsTo
    {
        return $this->belongsTo(GrupoPromocao::class, 'grupo_promocao_id');
    }
}
