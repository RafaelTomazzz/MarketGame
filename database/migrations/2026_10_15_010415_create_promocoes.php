<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promocoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jogo_id')->constrained('jogos')->onDelete('cascade');
            $table->enum('tipo', ['desconto', 'preco_fixo']);
            $table->integer('percentual')->nullable();
            $table->timestamp('data_fim');
            $table->decimal('valor', 7, 2);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocoes');
    }
};
