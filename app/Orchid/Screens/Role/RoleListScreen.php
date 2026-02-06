<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Role;

use Orchid\Platform\Models\Role;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD; // Import TD
use Orchid\Support\Facades\Layout; // Import Layout facade

class RoleListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            'roles' => Role::filters()->defaultSort('id', 'desc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Role Management';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all roles, including their permissions.';
    }

    public function permission(): ?iterable
    {
        return [
            'platform.systems.roles',
        ];
    }

    /**
     * The screen's action buttons.
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('bs.plus-circle')
                ->href(route('platform.systems.roles.create')),
        ];
    }

    /**
     * The screen's layout elements.
     */
    public function layout(): iterable
    {
        return [
            // We replaced 'RoleListLayout::class' with this inline table
            // so we can easily add the 'Edit Permissions' button.
            Layout::table('roles', [
                
                TD::make('name', 'Name')
                    ->sort()
                    ->cantHide()
                    ->filter(TD::FILTER_TEXT)
                    ->render(fn (Role $role) => Link::make($role->name)
                        ->route('platform.systems.roles.edit', $role->id)),

                TD::make('slug', 'Slug')
                    ->sort()
                    ->cantHide()
                    ->filter(TD::FILTER_TEXT),

                TD::make('created_at', 'Created')
                    ->sort()
                    ->render(fn (Role $role) => $role->created_at->toDateTimeString()),

                // 👇 THIS IS THE NEW EDIT BUTTON
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Role $role) => Link::make(__('Edit Permissions'))
                        ->route('platform.systems.roles.edit', $role->id)
                        ->icon('pencil')
                        ->class('btn btn-sm btn-link')),
            ]),
        ];
    }
}