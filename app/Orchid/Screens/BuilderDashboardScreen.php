<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Color;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;
use Orchid\Screen\Repository;

class BuilderDashboardScreen extends Screen
{
    public function permission(): ?iterable
    {
        // 👇 This locks the screen. Only users with this specific key can view it.
        return [
            'platform.builder.dashboard',
        ];
    }
    /**
     * Fetch data to be displayed on the screen.
     * * @return array
     */
    public function query(): iterable
    {
        // SRS FR-D-03: Analytics: views, conversions, CTR, lead quality score 
        // TODO: Replace these hardcoded values with actual DB queries later.
        return [
            'metrics' => [
                'Project Views' => '1,240',
                'Lead Conversions' => '45',
                'CTR' => '3.6%',
                'Quality Score' => '8.5/10',
            ],
            // SRS FR-D-02: Lead inbox preview 
            'recent_leads' => [
                new Repository(['name' => 'Rahul Sharma', 'interest' => 'Green Valley Phase 1', 'status' => 'New', 'date' => '10 mins ago']),
                new Repository(['name' => 'Priya Verma', 'interest' => 'Skyline Towers', 'status' => 'Contacted', 'date' => '1 hour ago']),
                new Repository(['name' => 'Amit Patel', 'interest' => 'Green Valley Phase 1', 'status' => 'Booking Requested', 'date' => '2 hours ago']),
            ],
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Builder Dashboard';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Overview of project performance and recent leads.';
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            // SRS FR-D-01: Project listings management 
            Link::make('Create New Project')
                ->icon('plus')
                ->route('platform.main'), // TODO: Change this to your actual 'create project' route

            Link::make('Upgrade Subscription')
                ->icon('star')
                ->type(Color::WARNING),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            // Section 1: Top Metrics (FR-D-03)
            Layout::metrics([
                'Total Views' => 'metrics.Project Views',
                'Conversions' => 'metrics.Lead Conversions',
                'Click Rate' => 'metrics.CTR',
                'Lead Quality' => 'metrics.Quality Score',
            ]),

            // Section 2: Recent Leads Table (FR-D-02)
            Layout::table('recent_leads', [
                TD::make('name', 'Buyer Name'),
                TD::make('interest', 'Project Interest'),
                TD::make('status', 'Status')
                    ->render(function ($lead) {
                        $color = match ($lead['status']) {
                            'New' => 'text-success',
                            'Booking Requested' => 'text-primary',
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>{$lead['status']}</span>";
                    }),
                TD::make('date', 'Time'),
            ])->title('Recent Leads'),
        ];
    }
}