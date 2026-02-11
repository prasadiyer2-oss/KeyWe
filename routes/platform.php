<?php

declare(strict_types=1);


use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use App\Orchid\Screens\BuilderDashboardScreen;
use App\Orchid\Screens\BuilderProjectListScreen;
use App\Orchid\Screens\BuilderLeadListScreen;
use App\Orchid\Screens\BuilderSubscriptionScreen;
use App\Orchid\Screens\BuilderProfileScreen;
use App\Orchid\Screens\AdminBuilderVerificationScreen;
use App\Orchid\Screens\BuilderPropertyListScreen;
use Illuminate\Support\Facades\Route;
use App\Orchid\Screens\AdminProjectVerificationScreen;
use App\Orchid\Screens\BuilderProjectEditScreen;
use App\Orchid\Screens\BuilderPropertyEditScreen;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', BuilderDashboardScreen::class)
    ->name('platform.main')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->push(__('Dashboard'), route('platform.main')));

Route::screen('projects', BuilderProjectListScreen::class)
    ->name('platform.builder.projects')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('My Projects'), route('platform.builder.projects')));
        
Route::screen('subscription', BuilderSubscriptionScreen::class)
    ->name('platform.builder.subscription')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Subscription'), route('platform.builder.subscription')));

 Route::screen('profile/company', BuilderProfileScreen::class)
    ->name('platform.builder.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Company Profile'), route('platform.builder.profile')));   

Route::screen('leads', BuilderLeadListScreen::class)
    ->name('platform.builder.leads')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Lead Inbox'), route('platform.builder.leads')));

Route::screen('admin/project-verification', AdminProjectVerificationScreen::class)
    ->name('platform.admin.project.verification') // Unique name
    ->breadcrumbs(fn ($trail) => $trail
        ->parent('platform.main')
        ->push('Project Verification', route('platform.admin.project.verification')));

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));
        
Route::screen('builder/properties/{property}/edit', BuilderPropertyEditScreen::class)
    ->name('platform.builder.properties.edit')
    ->breadcrumbs(fn ($trail) => $trail
        ->parent('platform.index') // or your main dashboard
        ->push('Properties', route('platform.builder.properties')) // Update with your actual list route name
        ->push('Edit Unit'));
        
Route::screen('admin/verification', AdminBuilderVerificationScreen::class)
    ->name('platform.admin.verification')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.main')
        ->push(__('Builder Verification'), route('platform.admin.verification')));
        Route::screen('builder/projects/{project}/edit', BuilderProjectEditScreen::class)
    ->name('platform.builder.projects.edit')
    ->breadcrumbs(fn ($trail) => $trail
        ->parent('platform.index') // or your main dashboard
        ->push('My Projects', route('platform.builder.projects')) // Adjust if your list route is named differently
        ->push('Edit Project'));
        
Route::screen('properties', BuilderPropertyListScreen::class)
    ->name('platform.builder.properties')
    ->breadcrumbs(fn ($trail) => $trail
        ->parent('platform.main')
        ->push('Properties', route('platform.builder.properties')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));

// Example...
// Route::screen('example', ExampleScreen::class)
//     ->name('platform.example')
//     ->breadcrumbs(fn (Trail $trail) => $trail
//         ->parent('platform.index')
//         ->push('Example Screen'));

// Route::screen('/examples/form/fields', ExampleFieldsScreen::class)->name('platform.example.fields');
// Route::screen('/examples/form/advanced', ExampleFieldsAdvancedScreen::class)->name('platform.example.advanced');
// Route::screen('/examples/form/editors', ExampleTextEditorsScreen::class)->name('platform.example.editors');
// Route::screen('/examples/form/actions', ExampleActionsScreen::class)->name('platform.example.actions');

// Route::screen('/examples/layouts', ExampleLayoutsScreen::class)->name('platform.example.layouts');
// Route::screen('/examples/grid', ExampleGridScreen::class)->name('platform.example.grid');
// Route::screen('/examples/charts', ExampleChartsScreen::class)->name('platform.example.charts');
// Route::screen('/examples/cards', ExampleCardsScreen::class)->name('platform.example.cards');

// Route::screen('idea', Idea::class, 'platform.screens.idea');
