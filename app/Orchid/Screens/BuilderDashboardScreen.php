<?php

namespace App\Orchid\Screens;

use App\Models\Project;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Color;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;

class BuilderDashboardScreen extends Screen
{
    // 1. Permission Check
    public function permission(): ?iterable
    {
        return [
            'platform.builder.dashboard',
        ];
    }

    // 2. Fetch REAL Data
    public function query(): iterable
    {
        $user = Auth::user();

        // Fetch projects created by this builder
        $projects = Project::where('user_id', $user->id)
            ->withCount('leads') // Count leads for each project efficiently
            ->latest()
            ->limit(5)
            ->get();

        // Calculate simple real metrics
        $totalProjects = Project::where('user_id', $user->id)->count();
        $totalLeads = Lead::whereHas('project', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        return [
            'metrics' => [
                'Total Projects' => $totalProjects,
                'Total Leads' => $totalLeads,
                'Pending Leads' => '0', // Placeholder until we have status logic
                'Conversion Rate' => '0%', // Placeholder until we have logic
            ],

            // Replaced 'recent_leads' with 'projects'
            'projects' => $projects,
        ];
    }

    public function name(): ?string
    {
        return 'Builder Dashboard';
    }

    public function description(): ?string
    {
        return 'Overview of your active projects and lead statistics.';
    }

    public function commandBar(): iterable
    {
        return [
            Link::make('Create New Project')
                ->icon('plus')
                ->route('platform.builder.projects'), // Linked to your existing Project List

            Link::make('Lead Inbox')
                ->icon('envelope-paper')
                ->route('platform.builder.leads'), // We will build this next
        ];
    }

    public function layout(): iterable
    {
        return [
            // Section 1: Real Metrics
            Layout::metrics([
                'Active Projects' => 'metrics.Total Projects',
                'Total Leads' => 'metrics.Total Leads',
                'Pending Actions' => 'metrics.Pending Leads',
                'Avg Conversion' => 'metrics.Conversion Rate',
            ]),

            // Section 2: Recent Projects Table (From DB)
            Layout::table('projects', [

                TD::make('name', 'Project Name') // ✅ matches your database column
                    ->sort(), // You don't even need the ->render()
                TD::make('location', 'Location'),

                TD::make('status', 'Status')
                    ->render(function (Project $project) {
                        $color = match ($project->status) {
                            'approved' => 'text-success',
                            'pending' => 'text-warning',
                            'rejected' => 'text-danger',
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>" . ucfirst($project->status) . "</span>";
                    }),

                TD::make('leads_count', 'Total Leads')
                    ->align(TD::ALIGN_CENTER)
                    ->render(fn(Project $project) => $project->leads_count),

                // The Action Button
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(
                        fn(Project $project) =>
                        Link::make('View Leads')
                            ->icon('eye')
                            ->route('platform.builder.leads', ['project_id' => $project->id])
                            ->class('btn btn-sm btn-link')
                    ),
            ])->title('Recent Projects'),
        ];
    }
}