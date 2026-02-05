<?php

namespace App\Orchid\Screens;

use App\Models\Project;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload; // <--- Ensure this is imported
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

class BuilderProjectListScreen extends Screen
{
    // ... (query, permission, name, description, commandBar methods remain the same) ...

    public function query(): iterable
    {
        return [
            'projects' => Project::where('user_id', Auth::id())
                ->latest()
                ->paginate(10)
        ];
    }

    public function permission(): ?iterable
    {
        return [
            'platform.builder.projects',
        ];
    }

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
            // 1. Project List Table
            Layout::table('projects', [
                TD::make('name', 'Project Name')
                    ->sort()
                    ->render(fn (Project $project) => "<strong>{$project->name}</strong>"),
                
                TD::make('location', 'Location')->sort(),

                TD::make('status', 'Status')
                    ->sort()
                    ->render(fn (Project $project) => match ($project->status) {
                        'Ongoing' => "<span class='text-primary'>● {$project->status}</span>",
                        'Completed' => "<span class='text-success'>● {$project->status}</span>",
                        'Upcoming' => "<span class='text-warning'>● {$project->status}</span>",
                        default => "<span class='text-muted'>● {$project->status}</span>",
                    }),

                TD::make('verification_status', 'KeyWe Verification') 
                    ->sort()
                    ->render(fn (Project $project) => $project->verification_status === 'Verified' 
                        ? '<span class="badge bg-success">Verified</span>' 
                        : '<span class="badge bg-secondary">' . $project->verification_status . '</span>'),

                TD::make('total_units', 'Total Units')->align(TD::ALIGN_RIGHT),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Project $project) => Link::make('Edit')
                        ->route('platform.builder.projects.edit', $project->id)
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. Create Project Modal (UPDATED WITH UPLOAD)
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

                Input::make('project.total_units')
                    ->title('Total Units')
                    ->type('number'),

                // --- NEW IMAGE UPLOAD FIELD ---
                Upload::make('project.attachment')
                    ->title('Project Images')
                    ->groups('project_images') // Distinct group name
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Register New Project')
               ->applyButton('Create Project')
               ->closeButton('Cancel'),
        ];
    }

    /**
     * Create Project Logic (UPDATED TO SYNC IMAGES)
     */
    public function createProject(Request $request)
    {
        $data = $request->validate([
            'project.name' => 'required|string',
            'project.location' => 'required|string',
            'project.project_type' => 'required',
            'project.rera_number' => 'nullable|string',
            'project.total_units' => 'numeric',
            // Validate attachment
            'project.attachment' => 'array', 
        ])['project'];

        $data['user_id'] = Auth::id();
        $data['status'] = 'Upcoming';
        $data['verification_status'] = 'Draft';

        $project = Project::create($data);

        // SYNC IMAGES TO THE NEW PROJECT
        $project->attachment()->sync($request->input('project.attachment', []));

        Toast::info('Project "' . $data['name'] . '" created successfully.');
    }
}