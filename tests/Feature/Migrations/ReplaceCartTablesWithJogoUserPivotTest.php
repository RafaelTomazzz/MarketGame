<?php

namespace Tests\Feature\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ReplaceCartTablesWithJogoUserPivotTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_migration_creates_pivot_when_legacy_tables_are_absent(): void
    {
        Schema::drop('jogo_user');

        $this->cartMigration()->up();

        $this->assertTrue(Schema::hasTable('jogo_user'));
        $this->assertFalse(Schema::hasTable('carrinhos'));
        $this->assertFalse(Schema::hasTable('item_carrinhos'));
    }

    public function test_migration_removes_empty_legacy_tables_and_creates_pivot(): void
    {
        Schema::drop('jogo_user');
        $this->createLegacyTables();

        $this->cartMigration()->up();

        $this->assertTrue(Schema::hasTable('jogo_user'));
        $this->assertFalse(Schema::hasTable('carrinhos'));
        $this->assertFalse(Schema::hasTable('item_carrinhos'));
    }

    public function test_migration_refuses_nonempty_legacy_tables_without_removing_data(): void
    {
        Schema::drop('jogo_user');
        $this->createLegacyTables();
        DB::table('carrinhos')->insert(['created_at' => now(), 'updated_at' => now()]);

        try {
            $this->cartMigration()->up();
            self::fail('A migration deveria recusar dados legados sem mapeamento de usuário.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('carrinho_id → user_id', $exception->getMessage());
        }

        $this->assertDatabaseCount('carrinhos', 1);
        $this->assertTrue(Schema::hasTable('item_carrinhos'));
        $this->assertFalse(Schema::hasTable('jogo_user'));
    }

    public function test_rollback_refuses_to_discard_new_cart_units(): void
    {
        DB::table('jogo_user')->insert([
            'user_id' => $this->createUser(),
            'jogo_id' => $this->createGame(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->cartMigration()->down();
            self::fail('A reversão deveria recusar unidades que não cabem no esquema legado.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('sem perda de dados', $exception->getMessage());
        }

        $this->assertDatabaseCount('jogo_user', 1);
        $this->assertFalse(Schema::hasTable('item_carrinhos'));
    }

    public function test_rollback_recreates_empty_legacy_tables_when_pivot_has_no_units(): void
    {
        $this->cartMigration()->down();

        $this->assertFalse(Schema::hasTable('jogo_user'));
        $this->assertTrue(Schema::hasColumns('carrinhos', ['id', 'created_at', 'updated_at']));
        $this->assertTrue(Schema::hasColumns('item_carrinhos', [
            'id',
            'carrinho_id',
            'jogo_id',
            'quantidade',
            'created_at',
            'updated_at',
        ]));
    }

    private function createLegacyTables(): void
    {
        Schema::create('carrinhos', function ($table): void {
            $table->id();
            $table->timestamps();
        });

        Schema::create('item_carrinhos', function ($table): void {
            $table->id();
            $table->foreignId('carrinho_id')->constrained('carrinhos')->cascadeOnDelete();
            $table->foreignId('jogo_id')->constrained('jogos')->cascadeOnDelete();
            $table->integer('quantidade')->default(1);
            $table->timestamps();
        });
    }

    private function cartMigration(): Migration
    {
        return require database_path(
            'migrations/2026_10_26_220936_replace_cart_tables_with_jogo_user_pivot.php'
        );
    }

    private function createUser(): int
    {
        return DB::table('users')->insertGetId([
            'nome' => 'Usuário de teste',
            'email' => 'migration@example.test',
            'cpf' => '12345678909',
            'telefone' => '11987654321',
            'password' => bcrypt('senha-segura'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createGame(): int
    {
        return DB::table('jogos')->insertGetId([
            'nome' => 'Jogo de teste',
            'slug' => 'jogo-de-teste-migration',
            'descricao' => 'Descrição do jogo de teste.',
            'preco' => '59.90',
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
