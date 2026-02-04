<?php

namespace App\Orchid\Screens;

use App\Models\Project; // <--- Import Model
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

class BuilderProjectListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        // Fetch projects for the currently logged-in builder
        return [
            'projects' => Project::where('user_id', Auth::id())
                ->latest()
                ->paginate(10) // Standard pagination
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'My Projects';
    }

    public function description(): ?string
    {
        return 'Manage your property listings and track verification status.';
    }

    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add New Project')
                ->modal('createProjectModal')
                ->method('createProject')
                ->icon('plus')
                ->type(Color::SUCCESS),
        ];
    }

    public function layout(): iterable
    {
        return [
            // 1. Project List Table (From DB)
            Layout::table('projects', [
                TD::make('name', 'Project Name')
                    ->sort()
                    ->render(fn (Project $project) => "<strong>{$project->name}</strong>"),
                
                TD::make('location', 'Location')
                    ->sort(),

                TD::make('status', 'Status')
                    ->sort()
                    ->render(function (Project $project) {
                        $color = match ($project->status) {
                            'Ongoing' => 'text-primary',
                            'Completed' => 'text-success',
                            'Upcoming' => 'text-warning',
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>● {$project->status}</span>";
                    }),

                TD::make('verification_status', 'KeyWe Verification') 
                    ->sort()
                    ->render(function (Project $project) {
                        return $project->verification_status === 'Verified' 
                            ? '<span class="badge bg-success">Verified</span>' 
                            : '<span class="badge bg-secondary">' . $project->verification_status . '</span>';
                    }),

                TD::make('total_units', 'Total Units')
                    ->align(TD::ALIGN_RIGHT),

                TD::make('views_count', 'Views')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(Project $project) => number_format($project->views_count)),
                
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Project $project) => Button::make('Edit')
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. Create Project Modal
            Layout::modal('createProjectModal', Layout::rows([
                Input::make('project.name')
                    ->title('Project Name')
                    ->required()
                    ->placeholder('Green Valley Phase 1'),

                Input::make('project.location')
                    ->title('Location')
                    ->required()
                    ->placeholder('City, Area'),

                Select::make('project.project_type')
                    ->title('Project Type')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                        'mixed' => 'Mixed Use',
                    ]),
                
                Input::make('project.rera_number')
                    ->title('RERA Number')
                    ->placeholder('P518000XXXXX'),

                // SRS FR-L-01: Builders input unit count
                Input::make('project.total_units')
                    ->title('Total Units')
                    ->type('number'),

            ]))->title('Register New Project')
               ->applyButton('Create Project')
               ->closeButton('Cancel'),
        ];
    }

    /**
     * Create Project Logic (Saving to DB)
     */
    public function createProject(Request $request)
    {
        $data = $request->validate([
            'project.name' => 'required|string',
            'project.location' => 'required|string',
            'project.project_type' => 'required',
            'project.rera_number' => 'nullable|string',
            'project.total_units' => 'numeric',
        ])['project'];

        // Assign to current user and set defaults
        $data['user_id'] = Auth::id();
        $data['status'] = 'Upcoming';
        $data['verification_status'] = 'Draft';

        Project::create($data);

        Toast::info('Project "' . $data['name'] . '" created successfully.');
    }
}