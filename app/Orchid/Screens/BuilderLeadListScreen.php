<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class BuilderLeadListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        return [
            // Dummy data for FR-D-02 Lead Inbox
            'leads' => [
                new Repository([
                    'id' => 101,
                    'name' => 'Rahul Sharma',
                    'project' => 'Green Valley Phase 1',
                    'contact' => '+91 98765 43210',
                    'status' => 'New', // Needs Amber color
                    'time' => '10 mins ago',
                    'note' => 'Looking for 2BHK, immediate possession.',
                ]),
                new Repository([
                    'id' => 102,
                    'name' => 'Priya Verma',
                    'project' => 'Skyline Towers',
                    'contact' => '+91 99887 76655',
                    'status' => 'Booked', // Needs Green color
                    'time' => '1 hour ago',
                    'note' => 'Site visit scheduled for Sunday.',
                ]),
                new Repository([
                    'id' => 103,
                    'name' => 'Amit Patel',
                    'project' => 'Green Valley Phase 1',
                    'contact' => 'Hidden (Unlock to view)',
                    'status' => 'Contacted',
                    'time' => '2 hours ago',
                    'note' => 'Asked about loan options.',
                ]),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Lead Inbox';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Manage interested buyers and site visit requests.';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            // 1. Leads Table
            Layout::table('leads', [
                TD::make('name', 'Buyer Name')
                    ->sort()
                    ->render(fn ($lead) => "<strong>{$lead['name']}</strong>"),

                TD::make('project', 'Project Interest'),

                TD::make('status', 'Status')
                    ->render(function ($lead) {
                        // Applying Brand Colors 
                        // Amber for urgency (New), Green for success (Booked)
                        $color = match ($lead['status']) {
                            'New' => 'text-warning', // Bootstrap warning is close to Amber
                            'Booked' => 'text-success', // Bootstrap success matches KeyWe Green
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>● {$lead['status']}</span>";
                    }),

                TD::make('time', 'Received'),

                // Action: View Details (Simulates downloading lead pack)
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn ($lead) => ModalToggle::make('View Details')
                        ->modal('leadDetailsModal')
                        ->method('markAsContacted')
                        ->asyncParameters(['lead_id' => $lead['id']]) // Pass ID to modal
                        ->icon('eye')
                        ->type(Color::LIGHT)),
            ]),

            // 2. Lead Details Modal
            Layout::modal('leadDetailsModal', Layout::rows([
                // In a real app, these would be populated via the 'async' method
                Input::make('lead.name')
                    ->title('Buyer Name')
                    ->readonly(),

                Input::make('lead.contact')
                    ->title('Phone Number')
                    ->readonly()
                    ->help('Contact strictly within business hours.'),

                TextArea::make('lead.note')
                    ->title('Buyer Requirements')
                    ->rows(3)
                    ->readonly(),

            ]))->title('Buyer Lead Pack')
               ->applyButton('Mark as Contacted')
               ->closeButton('Close'),
        ];
    }

    /**
     * Handle the "Mark as Contacted" action
     */
    public function markAsContacted(Request $request)
    {
        Toast::info('Lead status updated to "Contacted".');
    }
}