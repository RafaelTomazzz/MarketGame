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
        Schema::create('jogos', function (Blueprint $table) {
            $table->id();

            $table->string('nome');
            $table->string('slug')->unique();
            $table->text('descricao');
            $table->decimal('preco', 6, 2);
            $table->string('imagem')->nullable();
            $table->string('desenvolvedor');
            $table->timestamp('data_lancamento');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jogos');
    }
};
