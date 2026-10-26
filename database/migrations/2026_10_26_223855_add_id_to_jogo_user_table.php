<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::rename('jogo_user', 'jogo_user_without_id');
            $this->createPivotTable('jogo_user', true);

            DB::table('jogo_user')->insertUsing(
                ['user_id', 'jogo_id', 'created_at', 'updated_at'],
                DB::table('jogo_user_without_id')->select([
                    'user_id',
                    'jogo_id',
                    'created_at',
                    'updated_at',
                ]),
            );

            Schema::drop('jogo_user_without_id');

            return;
        }

        Schema::table('jogo_user', function (Blueprint $table): void {
            $table->id();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('jogo_user') && DB::table('jogo_user')->exists()) {
            throw new RuntimeException(
                'Não é possível remover os IDs das unidades enquanto jogo_user contiver dados; '
                .'a reversão invalidaria IDs usados pelo código.'
            );
        }

        Schema::dropIfExists('jogo_user');
        $this->createPivotTable('jogo_user', false);
    }

    private function createPivotTable(string $tableName, bool $withId): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($withId): void {
            if ($withId) {
                $table->id();
            }

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jogo_id')->constrained('jogos')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
