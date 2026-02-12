<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);
    }

    /**
     * Register the application menu.
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index'))
                ->permission('platform.builder.dashboard'),

            Menu::make('My Projects')
                ->icon('bs.building')
                ->route('platform.builder.projects')
                ->title('Property Management')
                ->permission('platform.builder.projects'),

            Menu::make('Lead Inbox')
                ->icon('bs.envelope-paper')
                ->route('platform.builder.leads')
                ->badge(fn() => 5, Color::DANGER),

            Menu::make('Subscription')
                ->icon('bs.credit-card')
                ->route('platform.builder.subscription')
                ->title('Account Settings'),

            Menu::make('Company Profile')
                ->icon('bs.building-gear')
                ->route('platform.builder.profile'),

            // --- SUPER ADMIN SECTION ---

            Menu::make('Builder Verification')
                ->icon('bs.shield-check')
                ->route('platform.admin.verification')
                ->title('Super Admin Controls')
                ->badge(fn() => 3, Color::DANGER)
                // 👇 FIXED: Matching the permission key defined below
                ->permission('platform.admin.builder.verification'),

            Menu::make('Project Verification')
                ->icon('bs.building-check')
                ->route('platform.admin.project.verification')
                ->title('Approvals')
                // 👇 FIXED: Matching the permission key defined below
                ->permission('platform.admin.project.verification'),
            Menu::make('Properties')
                ->icon('bs.house-door')
                ->route('platform.builder.properties')
                ->title('Inventory')
                ->permission('platform.builder.properties'),

            // --- SYSTEM SECTION ---

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('Documentation')
                ->title('Docs')
                ->icon('bs.box-arrow-up-right')
                ->url('https://orchid.software/en/docs')
                ->target('_blank'),

            Menu::make('Changelog')
                ->icon('bs.box-arrow-up-right')
                ->url('https://github.com/orchidsoftware/platform/blob/master/CHANGELOG.md')
                ->target('_blank')
                ->badge(fn() => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))

                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
            ItemPermission::group(__('System')),



            ItemPermission::group(__('Builder Controls'))
                ->addPermission('platform.builder.dashboard', __('Access Builder Dashboard'))
                ->addPermission('platform.builder.projects', __('Manage Projects (Create/Edit)'))
                ->addPermission('platform.builder.properties', __('Manage Units/Properties'))
                ->addPermission('platform.builder.leads', __('Manage leads')),

            ItemPermission::group(__('Admin Verification'))
                // 👇 These keys must match the menu() keys exactly!
                ->addPermission('platform.admin.project.verification', __('Verify Projects'))
                ->addPermission('platform.admin.builder.verification', __('Verify Builders')),
        ];
    }
}