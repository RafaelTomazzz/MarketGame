<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'status',
        'total',
        'frete',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'frete' => 'decimal:2',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jogos(): BelongsToMany
    {
        return $this->belongsToMany(Jogo::class, 'pedido_jogos', 'pedido_id', 'jogo_id')
            ->withPivot(['quantidade', 'preco_original', 'preco_unitario'])
            ->withTimestamps();
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class, 'pedido_id');
    }
}
