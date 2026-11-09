<?php

namespace Tests\Feature;

use App\Admin\Controllers\AuthController as AppAuthController;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\OperationLog;
use Encore\Admin\Filament\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * M1 acceptance tests: the DSL bridge serves the built-in resource pages
 * and the form save pipeline runs the original saving hooks.
 */
class AdminM1Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh', ['--seed' => true]);
    }

    protected function actingAsAdmin(): static
    {
        return $this->actingAs(Administrator::first(), config('admin.auth.guard'));
    }

    public function test_users_list_page_renders(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/users'))
            ->assertOk()
            ->assertSee('Administrator');
    }

    public function test_roles_list_page_renders(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/roles'))
            ->assertOk()
            ->assertSee('administrator');
    }

    public function test_permissions_list_page_renders(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/permissions'))
            ->assertOk()
            ->assertSee('auth.management');
    }

    public function test_logs_list_page_renders(): void
    {
        OperationLog::create([
            'user_id' => 1,
            'path' => 'admin/auth/users',
            'method' => 'GET',
            'ip' => '127.0.0.1',
            'input' => json_encode([]),
        ]);

        $this->actingAsAdmin()
            ->get(admin_url('auth/logs'))
            ->assertOk()
            ->assertSee('admin/auth/users');
    }

    public function test_user_edit_page_renders(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/users/1/edit'))
            ->assertOk()
            ->assertSee('admin');
    }

    public function test_user_view_page_renders_with_roles(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/users/1'))
            ->assertOk()
            ->assertSee('Administrator');
    }

    public function test_form_store_pipeline_hashes_password(): void
    {
        $controller = new \Encore\Admin\Controllers\UserController();

        $id = $controller->store([
            'username' => 'newadmin',
            'name' => 'New Admin',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $user = Administrator::query()->where('username', 'newadmin')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_form_update_pipeline_hashes_changed_password(): void
    {
        $controller = new \Encore\Admin\Controllers\UserController();

        $controller->update(1, [
            'username' => 'admin',
            'name' => 'Administrator',
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
        ]);

        $this->assertTrue(Hash::check('new-secret', Administrator::find(1)->password));
    }

    public function test_navigation_is_built_from_the_menu_table(): void
    {
        $this->actingAsAdmin();

        $items = \Encore\Admin\Filament\MenuProvider::items();

        $uris = collect($items)
            ->map(fn ($item) => trim((string) parse_url($item->getUrl() ?? '', PHP_URL_PATH), '/'))
            ->all();

        // URIs served natively (resources/pages) are skipped — no duplicates.
        $this->assertNotContains('auth/users', $uris);
        $this->assertNotContains('auth/menu', $uris);
    }

    public function test_sidebar_has_no_duplicate_entries_for_native_pages(): void
    {
        $this->actingAsAdmin();

        $html = $this->get(admin_url('auth/users'))->getContent();

        // Split into sidebar item blocks and count the ones linking to
        // auth/users — exactly one (the native resource item; the DB menu
        // node is skipped).
        $blocks = preg_split('/(?=fi-sidebar-item")/', $html);

        $this->assertSame(1, collect($blocks)
            ->filter(fn ($block) => str_contains($block, 'href="'.admin_url('auth/users').'"'))
            ->count());
    }

    public function test_permission_bridge_denies_users_without_auth_management(): void
    {
        // Create a user with no roles/permissions.
        $user = Administrator::create([
            'username' => 'limited',
            'password' => Hash::make('password'),
            'name' => 'Limited',
        ]);

        $this->actingAs($user, config('admin.auth.guard'))
            ->get(admin_url('auth/users'))
            ->assertForbidden();
    }

    public function test_menu_tree_page_renders(): void
    {
        // The tree page serves 7 seeded menu records (same query the tree renders).
        $menuModel = config('admin.database.menu_model');

        $this->actingAsAdmin()
            ->get(admin_url('auth/menu'))
            ->assertOk()
            ->assertSee(trans('admin.menu'));

        $this->assertSame(7, $menuModel::count());
    }

    public function test_menu_tree_page_denies_users_without_auth_management(): void
    {
        $user = Administrator::create([
            'username' => 'limited2',
            'password' => Hash::make('password'),
            'name' => 'Limited 2',
        ]);

        $this->actingAs($user, config('admin.auth.guard'))
            ->get(admin_url('auth/menu'))
            ->assertForbidden();
    }

    public function test_runtime_locale_switching_via_session(): void
    {
        // Switch to Japanese through the topbar route.
        $this->get(admin_url('locale/ja'))
            ->assertRedirect();

        $this->get(admin_url('auth/login'))
            ->assertOk()
            ->assertSee('ユーザーID')
            ->assertSee('ログイン');
    }

    public function test_invalid_locale_is_ignored(): void
    {
        $this->get(admin_url('locale/de-DE-XX'))
            ->assertRedirect();

        $this->get(admin_url('auth/login'))
            ->assertOk()
            ->assertSee('Username');
    }

    public function test_locale_switcher_renders_in_the_topbar(): void
    {
        $this->actingAsAdmin()
            ->get(admin_url('auth/users'))
            ->assertOk()
            ->assertSee('日本語')
            ->assertSee('简体中文')
            ->assertSee(admin_url('locale/ja'));
    }

    public function test_language_pack_japanese_coverage_and_rendering(): void
    {
        // ja must cover every en key (no silent English fallback gaps).
        $en = require lang_path('en/admin.php');
        $ja = require lang_path('ja/admin.php');

        $flatten = function (array $lines, string $prefix = '') use (&$flatten): array {
            $out = [];
            foreach ($lines as $key => $value) {
                $path = $prefix === '' ? $key : $prefix.'.'.$key;
                if (is_array($value)) {
                    $out += $flatten($value, $path);
                } else {
                    $out[$path] = true;
                }
            }

            return $out;
        };

        $this->assertSame([], array_keys(array_diff_key($flatten($en), $flatten($ja))));

        // Switch the application to Japanese and open the login page.
        $this->app->setLocale('ja');

        $this->assertSame('ユーザーID', trans('admin.username'));

        $this->get(admin_url('auth/login'))
            ->assertOk()
            ->assertSee('ユーザーID');
    }

    public function test_login_page_uses_username_field(): void
    {
        $this->get(admin_url('auth/login'))
            ->assertOk()
            ->assertSee('Username');
    }

    public function test_auth_controller_class_is_wired(): void
    {
        $this->assertInstanceOf(AppAuthController::class, new (config('admin.auth.controller')));
        $this->assertInstanceOf(\Encore\Admin\Controllers\AuthController::class, new AppAuthController());
    }
}
