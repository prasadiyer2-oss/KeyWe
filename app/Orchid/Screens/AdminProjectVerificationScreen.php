<?php

namespace App\Orchid\Screens;

use App\Models\Project;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class AdminProjectVerificationScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            // Fetch ALL projects, sorted by "Pending" first
            'projects' => Project::with('builder') // Eager load the builder (User)
                ->orderByRaw("FIELD(verification_status, 'Pending', 'Draft', 'Verified', 'Rejected')")
                ->latest()
                ->paginate(10)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Project Verification';
    }

    public function description(): ?string
    {
        return 'Review and verify project listings submitted by builders.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('projects', [
                TD::make('name', 'Project Name')
                    ->sort()
                    ->render(fn (Project $project) => "<strong>{$project->name}</strong>"),

                TD::make('builder.name', 'Builder')
                    ->render(fn (Project $project) => $project->builder->name ?? 'Unknown'),

                TD::make('rera_number', 'RERA ID')
                    ->render(fn (Project $project) => $project->rera_number ?? '-'),

                TD::make('location', 'Location'),

                TD::make('verification_status', 'Status')
                    ->sort()
                    ->render(fn (Project $project) => match ($project->verification_status) {
                        'Verified' => "<span class='badge bg-success'>Verified</span>",
                        'Pending'  => "<span class='badge bg-warning text-dark'>Pending Action</span>",
                        'Rejected' => "<span class='badge bg-danger'>Rejected</span>",
                        default    => "<span class='badge bg-secondary'>Draft</span>",
                    }),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(function (Project $project) {
                        // If already verified, show nothing or a specific message
                        if ($project->verification_status === 'Verified') {
                             return Button::make('Reject')
                                ->icon('ban')
                                ->method('rejectProject')
                                ->confirm('Are you sure you want to revoke verification?')
                                ->parameters(['id' => $project->id])
                                ->type(Color::DANGER);
                        }

                        return Button::make('Approve')
                                ->icon('check')
                                ->method('approveProject')
                                ->confirm('Approve this project for public listing?')
                                ->parameters(['id' => $project->id])
                                ->type(Color::SUCCESS)
                                . ' ' .
                                Button::make('Reject')
                                ->icon('ban')
                                ->method('rejectProject')
                                ->confirm('Reject this project?')
                                ->parameters(['id' => $project->id])
                                ->type(Color::DANGER);
                    }),
            ]),
        ];
    }

    /**
     * Logic: Approve Project
     */
    public function approveProject(Request $request)
    {
        $project = Project::findOrFail($request->get('id'));
        
        $project->verification_status = 'Verified';
        $project->save();

        Toast::success("Project '{$project->name}' has been verified.");
    }

    /**
     * Logic: Reject Project
     */
    public function rejectProject(Request $request)
    {
        $project = Project::findOrFail($request->get('id'));
        
        $project->verification_status = 'Rejected';
        $project->save();

        Toast::warning("Project '{$project->name}' has been rejected.");
    }
}