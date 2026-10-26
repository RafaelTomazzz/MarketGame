<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFormaPagamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $numeroCartao = $this->input('numero_cartao');

        if (is_string($numeroCartao)) {
            $this->merge([
                'numero_cartao' => preg_replace('/\D+/', '', $numeroCartao),
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'in:debito,credito,pix'],
            'numero_cartao' => [
                'exclude_unless:tipo,debito,credito',
                'required',
                'string',
                'digits_between:13,19',
            ],
            'titular_cartao' => [
                'exclude_unless:tipo,debito,credito',
                'required',
                'string',
                'max:255',
            ],
            'bandeira' => [
                'exclude_unless:tipo,debito,credito',
                'required',
                'string',
                'in:Visa,Mastercard,Elo,American Express,Hipercard,Outra',
            ],
            'validade' => [
                'exclude_unless:tipo,debito,credito',
                'required',
                'date_format:Y-m',
            ],
            'cvv' => ['prohibited'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! in_array($this->input('tipo'), ['debito', 'credito'], true)) {
                    return;
                }

                $numeroCartao = $this->input('numero_cartao');

                if (! $validator->errors()->has('numero_cartao')
                    && is_string($numeroCartao)
                    && ! $this->passesLuhn($numeroCartao)) {
                    $validator->errors()->add(
                        'numero_cartao',
                        'O número do cartão informado não é válido.',
                    );
                }

                $validade = $this->input('validade');

                if (! $validator->errors()->has('validade')
                    && is_string($validade)
                    && $validade < now()->format('Y-m')) {
                    $validator->errors()->add(
                        'validade',
                        'A validade do cartão deve estar no mês atual ou no futuro.',
                    );
                }
            },
        ];
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        $dadosSensiveis = [
            'numero_cartao' => null,
            'cvv' => null,
        ];

        request()->merge($dadosSensiveis);
        $this->merge($dadosSensiveis);

        parent::failedValidation($validator);
    }

    private function passesLuhn(string $numeroCartao): bool
    {
        $soma = 0;
        $dobrar = false;

        for ($indice = strlen($numeroCartao) - 1; $indice >= 0; $indice--) {
            $digito = (int) $numeroCartao[$indice];

            if ($dobrar) {
                $digito *= 2;

                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $soma += $digito;
            $dobrar = ! $dobrar;
        }

        return $soma % 10 === 0;
    }
}
