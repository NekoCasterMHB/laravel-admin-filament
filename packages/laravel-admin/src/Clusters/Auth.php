<?php

namespace Encore\Admin\Clusters;

use Filament\Clusters\Cluster;

/**
 * "Auth management" cluster — reproduces the original /admin/auth/* URIs
 * (auth/users, auth/roles, auth/permissions, auth/logs).
 */
class Auth extends Cluster
{
    protected static ?string $slug = 'auth';

    /**
     * The sidebar renders the "Admin" expandable submenu (MenuProvider) —
     * no in-page sub-navigation on cluster pages.
     *
     * {@inheritdoc}
     */
    protected static bool $shouldRegisterSubNavigation = false;

    /**
     * The "Admin" group in the sidebar comes from the admin_menu table
     * (MenuProvider) — the cluster itself registers no item (its raw
     * /admin/auth URL has no page).
     *
     * {@inheritdoc}
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        // The original DB menu named this group "Admin" (parent of
        // Users/Roles/Permission/Menu/Operation log).
        return 'Admin';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'fas-users-gear';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }
}
