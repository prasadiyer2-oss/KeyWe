<?php

namespace App\Orchid\Screens;

use App\Models\Project;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

class BuilderProjectEditScreen extends Screen
{
    /**
     * @var Project
     */
    public $project;

    /**
     * Fetch data to be displayed on the screen.
     * Orchid automatically finds the project from the URL ID.
     */
    public function query(Project $project): iterable
    {
        // Security: Ensure the user owns this project
        if ($project->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to edit this project.');
        }

        return [
            'project' => $project
        ];
    }

    public function name(): ?string
    {
        return 'Edit Project: ' . $this->project->name;
    }

    public function description(): ?string
    {
        return 'Update project details and status.';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Changes')
                ->icon('check')
                ->method('save')
                ->type(Color::PRIMARY),

            Button::make('Delete Project')
                ->icon('trash')
                ->method('remove')
                ->confirm('Are you sure you want to delete this project permanently?')
                ->type(Color::DANGER),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                Input::make('project.name')
                    ->title('Project Name')
                    ->required(),

                Input::make('project.location')
                    ->title('Location')
                    ->required(),

                Select::make('project.project_type')
                    ->title('Project Type')
                    ->options([
                        'residential' => 'Residential',
                        'commercial' => 'Commercial',
                        'mixed' => 'Mixed Use',
                    ])
                    ->required(),

                Select::make('project.status')
                    ->title('Project Status')
                    ->options([
                        'Upcoming' => 'Upcoming',
                        'Ongoing' => 'Ongoing',
                        'Completed' => 'Completed',
                    ])
                    ->required(),
                
                Input::make('project.rera_number')
                    ->title('RERA Number'),

                Input::make('project.total_units')
                    ->title('Total Units')
                    ->type('number'),
            ])
        ];
    }

    /**
     * Logic to Update the Project
     */
    public function save(Project $project, Request $request)
    {
        // Security Check
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'project.name' => 'required|string',
            'project.location' => 'required|string',
            'project.project_type' => 'required',
            'project.status' => 'required',
            'project.rera_number' => 'nullable|string',
            'project.total_units' => 'numeric',
        ])['project'];

        $project->fill($data)->save();

        Toast::info('Project updated successfully.');

        // Redirect back to the list
        return redirect()->route('platform.builder.projects'); 
    }

    /**
     * Logic to Delete the Project
     */
    public function remove(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $project->delete();

        Toast::info('Project deleted successfully.');

        return redirect()->route('platform.builder.projects');
    }
}