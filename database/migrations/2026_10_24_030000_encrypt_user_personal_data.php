<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 512)->change();
            $table->string('telefone', 1024)->change();
        });

        DB::table('users')
            ->select(['id', 'cpf', 'telefone'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'cpf' => $this->encryptIfNeeded($user->cpf),
                            'telefone' => $this->encryptIfNeeded($user->telefone),
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->select(['id', 'cpf', 'telefone'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'cpf' => $this->decryptIfNeeded($user->cpf),
                            'telefone' => $this->decryptIfNeeded($user->telefone),
                        ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 14)->change();
            $table->string('telefone')->change();
        });
    }

    private function encryptIfNeeded(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            Crypt::decryptString($value);

            return $value;
        } catch (DecryptException) {
            return Crypt::encryptString($value);
        }
    }

    private function decryptIfNeeded(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return $value;
        }
    }
};
