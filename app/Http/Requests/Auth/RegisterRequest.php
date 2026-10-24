<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = redirect()
            ->to($this->getRedirectUrl())
            ->withInput($this->except([
                '_token',
                'cpf',
                'telefone',
                'password',
                'password_confirmation',
            ]))
            ->withErrors($validator, $this->errorBag);

        throw new ValidationException($validator, $response, $this->errorBag);
    }

    protected function prepareForValidation(): void
    {
        $uf = $this->input('uf');

        $this->merge([
            'cpf' => $this->normalizarDigitos('cpf'),
            'telefone' => $this->normalizarDigitos('telefone'),
            'cep' => $this->normalizarDigitos('cep'),
            'uf' => is_string($uf) ? strtoupper(trim($uf)) : $uf,
        ]);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:20'],
            'cpf' => [
                'bail',
                'required',
                'string',
                'digits:11',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->cpfValido($value)) {
                        $fail('O CPF informado é inválido.');

                        return;
                    }

                    $cpfJaCadastrado = User::withTrashed()
                        ->get(['cpf'])
                        ->contains(fn (User $usuario): bool => preg_replace('/\D/', '', $usuario->cpf) === $value);

                    if ($cpfJaCadastrado) {
                        $fail('O CPF informado já está cadastrado.');
                    }
                },
            ],
            'telefone' => ['required', 'string', 'regex:/^(?:[1-9]\d[2-5]\d{7}|[1-9]\d9\d{8})$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
            'cep' => ['required', 'string', 'digits:8'],
            'uf' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'cidade' => ['required', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'rua' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:255'],
            'complemento' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function normalizarDigitos(string $campo): mixed
    {
        $valor = $this->input($campo);

        return is_string($valor) ? preg_replace('/\D/', '', $valor) : $valor;
    }

    private function cpfValido(string $cpf): bool
    {
        if (preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        $digitos = array_map('intval', str_split($cpf));

        $soma = 0;
        for ($indice = 0; $indice < 9; $indice++) {
            $soma += $digitos[$indice] * (10 - $indice);
        }
        $primeiroDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);

        $soma = 0;
        for ($indice = 0; $indice < 10; $indice++) {
            $soma += $digitos[$indice] * (11 - $indice);
        }
        $segundoDigito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);

        return $digitos[9] === $primeiroDigito && $digitos[10] === $segundoDigito;
    }
}
