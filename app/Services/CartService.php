<?php

namespace App\Services;

use App\Models\Jogo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CartService
{
    public const FRETE_CENTAVOS = 500;

    /**
     * @return array{
     *     carrinhoItens: Collection<int, Jogo>,
     *     subtotal: string,
     *     frete: string,
     *     total: string
     * }
     */
    public function obterCarrinho(User $user): array
    {
        $carrinhoItens = $user->jogosNoCarrinho()
            ->withPivot('id')
            ->with([
                'categorias',
                'promocao' => fn (HasOne $query) => $query->where('data_fim', '>=', now()),
            ])
            ->orderByPivot('created_at')
            ->orderByPivot('jogo_id')
            ->get();

        $subtotalCentavos = $this->calcularSubtotalEmCentavos($carrinhoItens);
        $freteCentavos = $carrinhoItens->isEmpty() ? 0 : self::FRETE_CENTAVOS;

        return [
            'carrinhoItens' => $carrinhoItens,
            'subtotal' => $this->formatarCentavos($subtotalCentavos),
            'frete' => $this->formatarCentavos($freteCentavos),
            'total' => $this->formatarCentavos($subtotalCentavos + $freteCentavos),
        ];
    }

    public function adicionarJogo(User $user, Jogo $jogo): void
    {
        $user->jogosNoCarrinho()->attach($jogo->id);
    }

    public function removerUnidade(User $user, int $itemId): bool
    {
        return DB::table('jogo_user')
            ->where('id', $itemId)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * @param  Collection<int, Jogo>  $carrinhoItens
     */
    public function calcularSubtotalEmCentavos(Collection $carrinhoItens): int
    {
        return $carrinhoItens->sum(function (Jogo $jogo): int {
            $preco = $jogo->promocao?->valor ?? $jogo->preco;

            return $this->paraCentavos((string) $preco);
        });
    }

    public function formatarCentavos(int $centavos): string
    {
        return intdiv($centavos, 100)
            .'.'
            .str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }

    private function paraCentavos(string $valor): int
    {
        [$reais, $centavos] = array_pad(explode('.', $valor, 2), 2, '0');

        return ((int) $reais * 100) + (int) str_pad(substr($centavos, 0, 2), 2, '0');
    }
}
