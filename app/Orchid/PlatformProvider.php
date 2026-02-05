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
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
            Menu::make('Dashboard')
                ->icon('bs.book')
                ->title('Navigation')
                ->route(config('platform.index')),

            Menu::make('My Projects')
            ->icon('bs.building') // Building icon fits the Builder role
            ->route('platform.builder.projects')
            ->title('Property Management')
            ->permission('platform.builder.projects'),

            Menu::make('Lead Inbox')
            ->icon('bs.envelope-paper') // Icon representing incoming leads
            ->route('platform.builder.leads')
            ->badge(fn () => 5, Color::DANGER),

            Menu::make('Subscription')
            ->icon('bs.credit-card') // Credit card icon for billing
            ->route('platform.builder.subscription')
            ->title('Account Settings'),

            Menu::make('Company Profile')
            ->icon('bs.building-gear') // Gear icon implies settings/management
            ->route('platform.builder.profile'),
            
            Menu::make('Builder Verification')
            ->icon('bs.shield-check') // Shield icon implies security/verification
            ->route('platform.admin.verification')
            ->title('Super Admin Controls') // Separate section
            ->badge(fn () => 3, Color::DANGER),

            Menu::make('Properties')
            ->icon('bs.house-door')
            ->route('platform.builder.properties')
            ->title('Inventory')
            ->permission('platform.builder.properties'),

            Menu::make('Project Verification')
            ->icon('bs.building-check') // Changed icon to distinguish from User verification
    ->route('platform.admin.project.verification')
    ->title('Approvals'),

            // Menu::make('Sample Screen')
            //     ->icon('bs.collection')
            //     ->route('platform.example')
            //     ->badge(fn () => 6),

            // Menu::make('Form Elements')
            //     ->icon('bs.card-list')
            //     ->route('platform.example.fields')
            //     ->active('*/examples/form/*'),

            // Menu::make('Layouts Overview')
            //     ->icon('bs.window-sidebar')
            //     ->route('platform.example.layouts'),

            // Menu::make('Grid System')
            //     ->icon('bs.columns-gap')
            //     ->route('platform.example.grid'),

            // Menu::make('Charts')
            //     ->icon('bs.bar-chart')
            //     ->route('platform.example.charts'),

            // Menu::make('Cards')
            //     ->icon('bs.card-text')
            //     ->route('platform.example.cards')
            //     ->divider(),

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
                ->badge(fn () => Dashboard::version(), Color::DARK),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
            ItemPermission::group(__('Builder Controls'))
                ->addPermission('platform.builder.projects', __('Manage Projects (Create/Edit)'))
                ->addPermission('platform.builder.properties', __('Manage Units/Properties')),
            ItemPermission::group(__('Admin Verification'))
                ->addPermission('platform.admin.project.verification', __('Verify Projects'))
                ->addPermission('platform.admin.builder.verification', __('Verify Builders')),
            
        ];
    }
}
