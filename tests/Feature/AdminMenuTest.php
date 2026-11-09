<?php

namespace Tests\Feature;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Role;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Menu page parity with the original laravel-admin MenuController:
 * tree rendering, create/edit with roles, drag ordering, cascading delete.
 */
class AdminMenuTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', ['--seed' => true]);
        $this->actingAs(Administrator::first(), config('admin.auth.guard'));
    }

    public function test_tree_renders_all_seeded_nodes_with_their_links(): void
    {
        $html = $this->get(admin_url('auth/menu'))->assertOk()->getContent();

        foreach (['Dashboard', 'Admin', 'Users', 'Roles', 'Permission', 'Menu', 'Operation log'] as $title) {
            $this->assertStringContainsString($title, $html, "tree node [$title] missing");
        }

        // 7 seeded rows, each with a drag handle.
        $this->assertSame(7, substr_count($html, 'class="dd-handle"'));
    }

    public function test_create_persists_parent_uri_and_roles(): void
    {
        $role = Role::first();

        Livewire::test(\Encore\Admin\Controllers\MenuController::class)
            ->callAction('create', data: [
                'parent_id' => 2,
                'title' => 'Reports',
                'icon' => 'fa-chart-bar',
                'uri' => 'reports',
                'roles' => [$role->id],
                'permission' => 'dashboard',
            ])
            ->assertHasNoFormErrors();

        $menu = Menu::query()->where('title', 'Reports')->first();

        $this->assertNotNull($menu);
        $this->assertSame(2, (int) $menu->parent_id);
        $this->assertSame('reports', $menu->uri);
        $this->assertSame('fa-chart-bar', $menu->icon);
        $this->assertSame('dashboard', $menu->permission);
        $this->assertTrue($menu->roles()->whereKey($role->id)->exists(), 'roles pivot not saved');
    }

    public function test_edit_hydrates_and_saves_roles(): void
    {
        $admin = Menu::query()->where('title', 'Admin')->first();
        $role = Role::first();

        $component = Livewire::test(\Encore\Admin\Controllers\MenuController::class)
            ->call('mountTreeAction', 'edit', (string) $admin->getKey());

        // Existing binding is hydrated into the form (original behaviour).
        $this->assertSame(
            [(string) $role->id],
            array_map('strval', $component->get('mountedActions')[0]['data']['roles']),
        );

        $component
            ->set('mountedActions.0.data.title', 'Admin renamed')
            ->set('mountedActions.0.data.uri', '')
            ->call('callMountedAction')
            ->assertHasNoFormErrors();

        $this->assertSame('Admin renamed', $admin->fresh()->title);
        $this->assertTrue($admin->fresh()->roles()->whereKey($role->id)->exists());
    }

    public function test_drag_payload_persists_parent_and_order(): void
    {
        $menu = Menu::query()->where('title', 'Menu')->first();
        $users = Menu::query()->where('title', 'Users')->first();
        $roles = Menu::query()->where('title', 'Roles')->first();

        Livewire::test(\Encore\Admin\Controllers\MenuController::class)
            ->call('updateTree', [[
                'id' => $menu->id,
                'children' => [
                    ['id' => $users->id],
                    ['id' => $roles->id],
                ],
            ]]);

        $this->assertSame(0, (int) $menu->fresh()->parent_id);
        $this->assertSame($menu->id, (int) $users->fresh()->parent_id);
        $this->assertSame($menu->id, (int) $roles->fresh()->parent_id);
        $this->assertGreaterThan(0, (int) $users->fresh()->order);
    }

    public function test_delete_cascades_to_children(): void
    {
        $parent = Menu::query()->where('title', 'Admin')->first();

        $child = Menu::create([
            'parent_id' => $parent->id,
            'order' => 99,
            'title' => 'Temp child',
            'icon' => 'fa-bars',
            'uri' => 'temp',
        ]);

        $parent->delete();

        $this->assertNull(Menu::find($parent->id));
        $this->assertNull(Menu::find($child->id), 'children were not cascaded');
    }
}
