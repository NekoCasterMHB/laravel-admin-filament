<?php

namespace Encore\Admin\Filament;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Support\IconResolver;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

/**
 * Builds the panel navigation from the admin_menu table (the original
 * sidebar menu), honoring:
 *
 * - parent/child tree structure with `order` sorting. A parent node such
 *   as "Admin" becomes a collapsible NAVIGATION GROUP, so each child keeps
 *   its own icon — Filament renders nested child *items* with dots instead
 *   of icons when the sidebar is not collapsible on desktop.
 * - legacy fa-* icons via IconResolver
 * - role binding (admin_role_menu, admin.check_menu_roles)
 * - permission binding (admin_menu.permission, admin.menu_bind_permission)
 * - `admin.menu_exclude` URIs
 * - active-state highlighting per item (isActiveWhen)
 */
class MenuProvider
{
    /**
     * Navigation groups + items from the DB menu tree.
     *
     * @return array{groups: array<NavigationGroup>, items: array<NavigationItem>}
     */
    public static function navigation(): array
    {
        $groups = [];
        $items = [];

        foreach (Admin::menu() as $node) {
            $icon = IconResolver::resolve($node['icon'] ?? null, fallback: '');

            // A parent node becomes a collapsible group whose children are
            // flat, icon-bearing items.
            if (!empty($node['children'])) {
                // Port note: Filament forbids a group icon when its items
                // also have icons ("Either the group or its items can have
                // icons, but not both"). The laravel-admin menu shows an
                // icon per entry, so the group keeps only its label.
                $group = NavigationGroup::make($node['title'])
                    ->collapsible();

                $childItems = [];

                foreach ($node['children'] as $child) {
                    $item = static::buildItem($child, group: $node['title']);

                    if ($item !== null) {
                        $childItems[] = $item;
                    }
                }

                if ($childItems !== []) {
                    // Items belong to the group itself (the builder renders
                    // group->getItems()); top-level `items` stay flat.
                    $group->items($childItems);

                    $groups[] = $group;
                }

                continue;
            }

            $item = static::buildItem($node);

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return ['groups' => $groups, 'items' => $items];
    }

    /**
     * Flat item list (backwards-compatible helper used by tests).
     *
     * @return NavigationItem[]
     */
    public static function items(): array
    {
        return static::navigation()['items'];
    }

    /**
     * @param array       $node
     * @param string|null $group
     *
     * @return NavigationItem|null
     */
    protected static function buildItem(array $node, ?string $group = null): ?NavigationItem
    {
        if (!static::isVisible($node)) {
            return null;
        }

        if (in_array($node['uri'] ?? '', config('admin.menu_exclude', []), true)) {
            return null;
        }

        if (blank($node['uri'] ?? null) && empty($node['children'])) {
            return null;
        }

        $item = NavigationItem::make($node['title'])
            ->sort((int) $node['order']);

        if ($icon = IconResolver::resolve($node['icon'] ?? null, fallback: '')) {
            $item->icon($icon);
        }

        if ($group !== null) {
            $item->group($group);
        }

        if (filled($node['uri'] ?? null)) {
            $url = static::urlFor($node['uri']);

            $item->url($url)
                ->isActiveWhen(fn (): bool => rtrim(url()->current(), '/') === rtrim($url, '/'));
        }

        // Deeper nesting (3+ levels) keeps Filament's child-item rendering.
        $children = [];

        foreach ($node['children'] ?? [] as $child) {
            $childItem = static::buildItem($child);

            if ($childItem !== null) {
                $children[] = $childItem;
            }
        }

        if ($children !== []) {
            $item->childItems($children);
        }

        return $item;
    }

    /**
     * Map a menu uri to the panel URL. "/" is the panel home.
     *
     * @param string $uri
     *
     * @return string
     */
    protected static function urlFor(string $uri): string
    {
        if ($uri === '/') {
            return admin_url('');
        }

        return admin_url($uri);
    }

    /**
     * Role/permission visibility (original menu filtering semantics).
     *
     * @param array $node
     *
     * @return bool
     */
    protected static function isVisible(array $node): bool
    {
        $user = Admin::user();

        if (!$user) {
            return true;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        // Permission binding.
        if (config('admin.menu_bind_permission') && filled($node['permission'] ?? null)) {
            if ($user->cannot($node['permission'])) {
                return false;
            }
        }

        // Role binding.
        if (config('admin.check_menu_roles', true)) {
            $roles = collect($node['roles'] ?? [])->all();

            if (filled($roles)) {
                return $user->visible($roles);
            }
        }

        return true;
    }
}
