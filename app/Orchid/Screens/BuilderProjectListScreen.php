<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class BuilderProjectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // Dummy data simulating FR-D-01 Project Listings
        return [
            'projects' => [
                new Repository([
                    'id' => 1,
                    'name' => 'Green Valley Phase 1',
                    'location' => 'Navi Mumbai',
                    'units' => 120,
                    'status' => 'Ongoing',
                    'verification' => 'Verified', // FR-L-02
                    'views' => '1.2k',
                ]),
                new Repository([
                    'id' => 2,
                    'name' => 'Skyline Towers',
                    'location' => 'Thane',
                    'units' => 45,
                    'status' => 'Completed',
                    'verification' => 'Pending',
                    'views' => '850',
                ]),
                new Repository([
                    'id' => 3,
                    'name' => 'Oceanic Heights',
                    'location' => 'Bandra West',
                    'units' => 200,
                    'status' => 'Upcoming',
                    'verification' => 'Draft',
                    'views' => '0',
                ]),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'My Projects';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Manage your property listings and track verification status.';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            // Primary Action: Add Project (FR-D-01)
            ModalToggle::make('Add New Project')
                ->modal('createProjectModal')
                ->method('createProject')
                ->icon('plus')
                ->type(Color::SUCCESS), // KeyWe Green for Primary Actions 
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            // 1. The Project List Table
            Layout::table('projects', [
                TD::make('name', 'Project Name')
                    ->sort()
                    ->render(fn ($project) => "<strong>{$project['name']}</strong>"),
                
                TD::make('location', 'Location'),

                TD::make('status', 'Status')
                    ->render(function ($project) {
                        $color = match ($project['status']) {
                            'Ongoing' => 'text-primary',
                            'Completed' => 'text-success',
                            'Upcoming' => 'text-warning',
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>● {$project['status']}</span>";
                    }),

                TD::make('verification', 'KeyWe Verification') // FR-L-02 Verification Workflow
                    ->render(function ($project) {
                        return $project['verification'] === 'Verified' 
                            ? '<span class="badge bg-success">Verified</span>' 
                            : '<span class="badge bg-secondary">Processing</span>';
                    }),

                TD::make('units', 'Total Units')
                    ->align(TD::ALIGN_RIGHT),
                
                // Action Buttons per row
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn () => Button::make('Edit')
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. The Create Project Modal (Hidden by default, triggered by button)
            Layout::modal('createProjectModal', Layout::rows([
                Input::make('project.name')
                    ->title('Project Name')
                    ->placeholder('e.g. Green Valley Phase 1')
                    ->help('Enter the official RERA registered name.'),

                Input::make('project.location')
                    ->title('Location')
                    ->placeholder('City, Area'),

                Select::make('project.type')
                    ->title('Project Type')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                        'mixed' => 'Mixed Use',
                    ]),
                
                Input::make('project.rera')
                    ->title('RERA Number')
                    ->placeholder('Enter RERA ID for verification'),

            ]))->title('Register New Project')
               ->applyButton('Create Project')
               ->closeButton('Cancel'),
        ];
    }

    /**
     * Logic to handle the "Create Project" form submission
     */
    public function createProject(Request $request)
    {
        // In a real app, you would validate and save to DB here.
        // For now, we just show a toast notification.
        
        Toast::info('Project "' . $request->input('project.name') . '" created successfully. Verification pending.');
    }
}