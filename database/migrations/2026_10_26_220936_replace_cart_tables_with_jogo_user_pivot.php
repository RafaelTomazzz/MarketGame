<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyTablesWithData = [];

        foreach (['carrinhos', 'item_carrinhos'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                $legacyTablesWithData[] = $table;
            }
        }

        if ($legacyTablesWithData !== []) {
            throw new RuntimeException(
                'Não é possível remover tabelas legadas de carrinho com dados ('
                .implode(', ', $legacyTablesWithData)
                .'): não há mapeamento carrinho_id → user_id. Nenhum dado foi removido.'
            );
        }

        Schema::create('jogo_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('jogo_id')->constrained('jogos')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::dropIfExists('item_carrinhos');
        Schema::dropIfExists('carrinhos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('jogo_user') && DB::table('jogo_user')->exists()) {
            throw new RuntimeException(
                'Não é possível reverter o schema do carrinho enquanto jogo_user contiver unidades; '
                .'o esquema legado não representa essas unidades sem perda de dados.'
            );
        }

        Schema::dropIfExists('jogo_user');

        if (! Schema::hasTable('carrinhos')) {
            Schema::create('carrinhos', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('item_carrinhos')) {
            Schema::create('item_carrinhos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('carrinho_id')->constrained('carrinhos')->cascadeOnDelete();
                $table->foreignId('jogo_id')->constrained('jogos')->cascadeOnDelete();
                $table->integer('quantidade')->default(1);
                $table->timestamps();
            });
        }
    }
};
