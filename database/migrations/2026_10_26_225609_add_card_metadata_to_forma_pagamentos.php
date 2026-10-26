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
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->string('tipo')->nullable();
            $table->text('titular')->nullable();
            $table->string('bandeira')->nullable();
            $table->char('ultimos_digitos', 4)->nullable();
            $table->date('validade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forma_pagamentos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo',
                'titular',
                'bandeira',
                'ultimos_digitos',
                'validade',
            ]);
        });
    }
};
