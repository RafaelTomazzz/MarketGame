<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormaPagamento extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'tipo',
        'titular',
        'bandeira',
        'ultimos_digitos',
        'validade',
        'user_id',
    ];

    protected $hidden = [
        'titular',
    ];

    protected function casts(): array
    {
        return [
            'titular' => 'encrypted',
            'validade' => 'date:Y-m',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'forma_pagamento_id');
    }
}
