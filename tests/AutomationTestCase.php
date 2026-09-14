<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Builds the production-shaped tables the panel reads.
 *
 * None of these tables have migrations in this repo: they are owned by the
 * production Laravel app and the Python automation service, and several
 * columns (backend_id, order_id, screenshot_url, duration_seconds, logs.task_id)
 * exist only as production drift. The shapes below mirror `DESCRIBE` output
 * from social_slots_prod (2026-09-15).
 */
abstract class AutomationTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->assertInMemorySqlite();
        $this->buildSchema();
    }

    /**
     * The panel's .env points at a real MySQL database, and a cached
     * bootstrap/cache/config.php makes phpunit.xml's env overrides inert.
     * Refuse to build schema anywhere but the in-memory SQLite the tests expect.
     */
    private function assertInMemorySqlite(): void
    {
        $driver = DB::connection()->getDriverName();
        $database = DB::connection()->getDatabaseName();

        if ($driver !== 'sqlite' || $database !== ':memory:') {
            $this->fail(
                "Refusing to run: tests must use in-memory SQLite but the connection is {$driver}/{$database}. "
                .'Run `php artisan config:clear` (a cached config ignores phpunit.xml env).'
            );
        }
    }

    protected function buildSchema(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->morphs('tokenable');
            $t->string('name');
            $t->string('token', 64)->unique();
            $t->text('abilities')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamps();
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('guard_name');
            $t->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $t) {
            $t->unsignedBigInteger('role_id');
            $t->string('model_type');
            $t->unsignedBigInteger('model_id');
        });

        Schema::create('backend_games', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('backend_url')->default('');
            $t->string('username')->default('');
            $t->string('password')->default('');
            $t->string('game_url')->nullable();
            $t->string('image_url')->nullable();
            $t->tinyInteger('status')->default(1);
            $t->string('binding_key', 32)->nullable();
            $t->string('accounts_creation_pd')->default('');
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('backend_accounts', function (Blueprint $t) {
            $t->id();
            $t->string('game_id')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('backend_id')->nullable();
            $t->string('username');
            $t->string('password');
            $t->boolean('is_assigned')->default(false);
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('automation_results', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->text('description')->nullable();
            $t->char('task_id', 36)->nullable()->unique();
            $t->string('status')->nullable()->default('pending');
            $t->longText('data')->nullable();
            $t->timestamps();
            $t->unsignedBigInteger('backend_id')->nullable();
            $t->string('order_id', 50)->nullable();
            $t->string('screenshot_url')->nullable();
            $t->double('duration_seconds')->nullable();
        });

        Schema::create('automation_requests', function (Blueprint $t) {
            $t->id();
            $t->char('task_id', 36);
            $t->string('type');
            $t->longText('payload')->nullable();
            $t->timestamps();
            $t->integer('status_code')->nullable();
        });

        Schema::create('logs', function (Blueprint $t) {
            $t->id();
            $t->string('type')->default('info');
            $t->text('description');
            $t->string('source_url')->nullable();
            $t->timestamps();
            $t->unsignedBigInteger('backend_id')->nullable();
            $t->unsignedBigInteger('account_id')->nullable();
            $t->char('task_id', 36)->nullable();
        });

        Schema::create('wallet_detail', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('wallet_id')->default(0);
            $t->unsignedBigInteger('user_id')->default(0);
            $t->decimal('amount_minor', 18, 6)->nullable();
            $t->string('type');
            $t->string('provider', 32)->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Super Admin', 'guard_name' => 'web'],
            ['id' => 3, 'name' => 'Player', 'guard_name' => 'web'],
            ['id' => 4, 'name' => 'Employee', 'guard_name' => 'web'],
        ]);
    }

    protected function userWithRole(?string $role, string $email = null): User
    {
        $user = User::create([
            'first_name' => 'Test',
            'last_name' => $role ?? 'NoRole',
            'email' => $email ?? Str::lower(Str::random(8)).'@example.com',
            'password' => 'secret123',
        ]);

        if ($role) {
            $roleId = DB::table('roles')->where('name', $role)->value('id');
            DB::table('model_has_roles')->insert([
                'role_id' => $roleId,
                'model_type' => 'App\\Models\\User',
                'model_id' => $user->id,
            ]);
        }

        return $user;
    }

    protected function superAdmin(): User
    {
        return $this->userWithRole('Super Admin');
    }

    protected function player(): User
    {
        return $this->userWithRole('Player');
    }

    protected function backend(string $name, int $status = 1): int
    {
        return DB::table('backend_games')->insertGetId([
            'name' => $name,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Insert one automation_results row and its automation_requests row,
     * the way the Python service's insert_automation_result_and_request() does.
     */
    protected function task(array $result = [], array $request = []): string
    {
        $taskId = $result['task_id'] ?? (string) Str::uuid();

        DB::table('automation_results')->insert(array_merge([
            'task_id' => $taskId,
            'status' => 'success',
            'description' => 'Initiate account read',
            'backend_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $result));

        DB::table('automation_requests')->insert(array_merge([
            'task_id' => $taskId,
            'type' => 'read',
            'payload' => json_encode(['action' => 'read-account', 'backend' => 'juwa', 'account_id' => 'user_JW1']),
            'created_at' => now(),
            'updated_at' => now(),
        ], $request));

        return $taskId;
    }

    protected function log(array $attrs = []): int
    {
        return DB::table('logs')->insertGetId(array_merge([
            'type' => 'info',
            'description' => 'Read account action completed',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }
}
