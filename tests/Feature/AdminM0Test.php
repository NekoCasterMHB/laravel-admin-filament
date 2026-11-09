<?php

namespace Tests\Feature;

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * M0 acceptance tests: database parity with the original laravel-admin
 * schema, seed data, panel login and the operation log middleware.
 */
class AdminM0Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', ['--seed' => true]);
    }

    /**
     * The database must contain exactly the nine laravel-admin tables
     * (plus the framework `migrations` bookkeeping table) — nothing more,
     * nothing less, and the columns must match the original migration.
     */
    public function test_database_contains_exactly_the_original_admin_tables(): void
    {
        $tables = array_map(
            fn (string $table) => Str::afterLast($table, '.'),
            Schema::getTableListing(),
        );

        sort($tables);

        $this->assertSame([
            'admin_menu',
            'admin_operation_log',
            'admin_permissions',
            'admin_role_menu',
            'admin_role_permissions',
            'admin_role_users',
            'admin_roles',
            'admin_user_permissions',
            'admin_users',
            'migrations', // framework bookkeeping table
        ], $tables);
    }

    public function test_admin_tables_columns_match_the_original_schema(): void
    {
        $expected = [
            'admin_users' => [
                'id' => ['type' => 'integer', 'autoincrement' => true, 'nullable' => false],
                'username' => ['type' => 'varchar', 'nullable' => false],
                'password' => ['type' => 'varchar', 'nullable' => false],
                'name' => ['type' => 'varchar', 'nullable' => false],
                'avatar' => ['type' => 'varchar', 'nullable' => true],
                'remember_token' => ['type' => 'varchar', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => true],
                'updated_at' => ['type' => 'datetime', 'nullable' => true],
            ],
            'admin_menu' => [
                'id' => ['type' => 'integer', 'autoincrement' => true, 'nullable' => false],
                'parent_id' => ['type' => 'integer', 'nullable' => false],
                'order' => ['type' => 'integer', 'nullable' => false],
                'title' => ['type' => 'varchar', 'nullable' => false],
                'icon' => ['type' => 'varchar', 'nullable' => false],
                'uri' => ['type' => 'varchar', 'nullable' => true],
                'permission' => ['type' => 'varchar', 'nullable' => true],
                'created_at' => ['type' => 'datetime', 'nullable' => true],
                'updated_at' => ['type' => 'datetime', 'nullable' => true],
            ],
            'admin_operation_log' => [
                'id' => ['type' => 'integer', 'autoincrement' => true, 'nullable' => false],
                'user_id' => ['type' => 'integer', 'nullable' => false],
                'path' => ['type' => 'varchar', 'nullable' => false],
                'method' => ['type' => 'varchar', 'nullable' => false],
                'ip' => ['type' => 'varchar', 'nullable' => false],
                'input' => ['type' => 'text', 'nullable' => false],
                'created_at' => ['type' => 'datetime', 'nullable' => true],
                'updated_at' => ['type' => 'datetime', 'nullable' => true],
            ],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $name => $expectation) {
                $column = collect(Schema::getColumns($table))->firstWhere('name', $name);

                $this->assertNotNull($column, "[$table.$name] column is missing");
                $this->assertSame(
                    $expectation['type'],
                    strtolower($column['type']),
                    "[$table.$name] column type mismatch",
                );

                if (array_key_exists('autoincrement', $expectation)) {
                    $this->assertSame(
                        $expectation['autoincrement'],
                        (bool) ($column['auto_increment'] ?? $column['autoincrement'] ?? false),
                    );
                }

                $this->assertSame(
                    $expectation['nullable'],
                    (bool) $column['nullable'],
                    "[$table.$name] nullability mismatch",
                );
            }
        }
    }

    public function test_seeder_plants_the_original_data(): void
    {
        $this->assertDatabaseHas(config('admin.database.users_table'), [
            'username' => 'admin',
            'name' => 'Administrator',
        ]);

        $this->assertDatabaseHas(config('admin.database.roles_table'), [
            'slug' => 'administrator',
        ]);

        $this->assertDatabaseHas(config('admin.database.permissions_table'), [
            'slug' => '*',
        ]);

        $permissionSlugs = DB::table('admin_permissions')->pluck('slug')->all();

        sort($permissionSlugs);

        $this->assertSame([
            '*',
            'auth.login',
            'auth.management',
            'auth.setting',
            'dashboard',
        ], $permissionSlugs);

        $this->assertSame(7, DB::table('admin_menu')->count());

        $this->assertDatabaseHas('admin_menu', [
            'parent_id' => 0,
            'order' => 1,
            'title' => 'Dashboard',
            'icon' => 'fa-bar-chart',
            'uri' => '/',
        ]);

        // Menu rows carry timestamps (the original raw insert left them NULL).
        $this->assertSame(0, DB::table('admin_menu')->whereNull('created_at')->count());

        // "Admin" tree node bound to the administrator role.
        $this->assertDatabaseHas('admin_role_menu', [
            'role_id' => 1,
            'menu_id' => 2,
        ]);

        // Administrator user has the administrator role.
        $this->assertDatabaseHas('admin_role_users', [
            'role_id' => 1,
            'user_id' => 1,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(admin_base_path(''))
            ->assertRedirect(admin_base_path('auth/login'));
    }

    public function test_login_page_renders_username_form(): void
    {
        $this->get(admin_url('auth/login'))
            ->assertOk()
            ->assertSee('Username')
            ->assertSee('Remember me');
    }

    public function test_authenticated_user_can_open_the_panel_home(): void
    {
        $this->actingAs(Administrator::first(), config('admin.auth.guard'))
            ->get(admin_base_path(''))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_operation_log_middleware_records_panel_requests(): void
    {
        $this->actingAs(Administrator::first(), config('admin.auth.guard'))
            ->get(admin_base_path(''));

        $this->assertDatabaseHas('admin_operation_log', [
            'user_id' => 1,
            'path' => trim(config('admin.route.prefix'), '/'),
            'method' => 'GET',
        ]);
    }
}
