<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->user()?->getAuthIdentifier();

        return [
            'forma_pagamento_id' => [
                'required',
                'integer',
                Rule::exists('forma_pagamentos', 'id')
                    ->where(fn (Builder $query) => $query
                        ->where('user_id', $userId)
                        ->whereNull('deleted_at')),
            ],
        ];
    }
}
