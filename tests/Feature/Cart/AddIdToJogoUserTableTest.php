<?php

namespace Tests\Feature\Cart;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AddIdToJogoUserTableTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_migration_adds_unique_ids_and_preserves_duplicate_units_and_timestamps(): void
    {
        $this->cartItemIdMigration()->down();
        $userId = $this->createUser();
        $gameId = $this->createGame();

        DB::table('jogo_user')->insert([
            [
                'user_id' => $userId,
                'jogo_id' => $gameId,
                'created_at' => '2026-10-25 10:00:00',
                'updated_at' => '2026-10-25 10:00:00',
            ],
            [
                'user_id' => $userId,
                'jogo_id' => $gameId,
                'created_at' => '2026-10-25 11:00:00',
                'updated_at' => '2026-10-25 11:00:00',
            ],
        ]);

        $this->cartItemIdMigration()->up();

        $rows = DB::table('jogo_user')->orderBy('created_at')->get([
            'id',
            'user_id',
            'jogo_id',
            'created_at',
            'updated_at',
        ]);

        $this->assertTrue(Schema::hasColumn('jogo_user', 'id'));
        $this->assertSame(2, $rows->count());
        $this->assertCount(2, $rows->pluck('id')->unique());
        $this->assertSame([$userId, $userId], $rows->pluck('user_id')->all());
        $this->assertSame([$gameId, $gameId], $rows->pluck('jogo_id')->all());
        $this->assertSame(
            ['2026-10-25 10:00:00', '2026-10-25 11:00:00'],
            $rows->pluck('created_at')->all(),
        );
        $this->assertSame(
            ['2026-10-25 10:00:00', '2026-10-25 11:00:00'],
            $rows->pluck('updated_at')->all(),
        );
    }

    public function test_rollback_refuses_to_remove_ids_while_cart_contains_units(): void
    {
        DB::table('jogo_user')->insert([
            'user_id' => $this->createUser(),
            'jogo_id' => $this->createGame(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $this->cartItemIdMigration()->down();
            self::fail('A reversão deveria recusar linhas que dependem dos IDs do carrinho.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('invalidaria IDs usados pelo código', $exception->getMessage());
        }

        $this->assertTrue(Schema::hasColumn('jogo_user', 'id'));
        $this->assertDatabaseCount('jogo_user', 1);
    }

    public function test_rollback_restores_the_original_empty_pivot_schema(): void
    {
        $this->cartItemIdMigration()->down();

        $this->assertFalse(Schema::hasColumn('jogo_user', 'id'));
        $this->assertTrue(Schema::hasColumns('jogo_user', [
            'user_id',
            'jogo_id',
            'created_at',
            'updated_at',
        ]));
    }

    private function cartItemIdMigration(): Migration
    {
        return require database_path(
            'migrations/2026_10_26_223855_add_id_to_jogo_user_table.php'
        );
    }

    private function createUser(): int
    {
        return DB::table('users')->insertGetId([
            'nome' => 'Usuário de teste',
            'email' => 'migration-cart@example.test',
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
            'slug' => 'jogo-de-teste-migration-cart',
            'descricao' => 'Descrição do jogo de teste.',
            'preco' => '59.90',
            'desenvolvedor' => 'Estúdio de teste',
            'data_lancamento' => '2026-01-01 00:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
