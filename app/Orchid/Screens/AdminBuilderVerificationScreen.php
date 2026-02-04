<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Repository;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class AdminBuilderVerificationScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        // Dummy data simulating FR-S-02 (KYC Queue)
        return [
            'builders' => [
                new Repository([
                    'id' => 1,
                    'name' => 'Sunrise Estates Ltd',
                    'rera' => 'P51800009999',
                    'gst' => '27ABCDE1234F1Z5',
                    'submitted' => '2 hours ago',
                    'status' => 'Pending',
                    'doc_link' => 'rera_cert.pdf',
                ]),
                new Repository([
                    'id' => 2,
                    'name' => 'Urban Spaces Corp',
                    'rera' => 'P51800008888',
                    'gst' => '27FGHIJ5678K1Z9',
                    'submitted' => '1 day ago',
                    'status' => 'Pending',
                    'doc_link' => 'pan_card.jpg',
                ]),
                new Repository([
                    'id' => 3,
                    'name' => 'Lakeside Constructions',
                    'rera' => 'Invalid-Format',
                    'gst' => 'Pending',
                    'submitted' => '2 days ago',
                    'status' => 'Flagged', // Needs attention
                    'doc_link' => 'missing.pdf',
                ]),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Builder Verification Queue';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Review KYC documents and approve developer onboarding requests.';
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
            // Table of Pending Builders
            Layout::table('builders', [
                TD::make('name', 'Company Name')
                    ->sort()
                    ->render(fn ($builder) => "<strong>{$builder['name']}</strong>"),

                TD::make('rera', 'RERA ID'),
                TD::make('gst', 'GST Number'),
                
                TD::make('submitted', 'Submitted'),

                TD::make('status', 'KYC Status')
                    ->render(function ($builder) {
                        return match ($builder['status']) {
                            'Pending' => '<span class="text-warning">● Pending Review</span>',
                            'Flagged' => '<span class="text-danger">● Flagged</span>',
                            default => '<span class="text-muted">Unknown</span>',
                        };
                    }),

                // Actions Column
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->width('250px')
                    ->render(fn ($builder) => Group::make([
                        
                        // 1. View Docs Button (Opens Modal)
                        ModalToggle::make('Docs')
                            ->modal('reviewDocsModal')
                            ->method('approveBuilder') // This is just a placeholder, logic handled in modal
                            ->asyncParameters(['builder_id' => $builder['id']])
                            ->icon('eye')
                            ->type(Color::LIGHT),

                        // 2. Quick Approve Button
                        Button::make('Approve')
                            ->method('approveBuilder')
                            ->confirm('Are you sure you want to approve ' . $builder['name'] . '?')
                            ->parameters(['builder_id' => $builder['id']])
                            ->icon('check-circle')
                            ->type(Color::SUCCESS), // Green for Trust

                        // 3. Reject Button
                        Button::make('Reject')
                            ->method('rejectBuilder')
                            ->confirm('This will reject the onboarding request.')
                            ->parameters(['builder_id' => $builder['id']])
                            ->icon('x-circle')
                            ->type(Color::DANGER), // Red for Reject
                    ])),
            ]),

            // Document Review Modal
            Layout::modal('reviewDocsModal', Layout::rows([
                Input::make('builder.name')
                    ->title('Company Name')
                    ->readonly(),
                
                Input::make('builder.rera')
                    ->title('RERA ID')
                    ->readonly(),

                // Simulating a document viewer link
                \Orchid\Screen\Fields\Label::make('builder.doc_link')
                    ->title('Uploaded Documents')
                    ->value('<a href="#" target="_blank" class="text-primary text-decoration-underline">Download RERA Certificate (PDF)</a>')
                    ->popover('Click to open the uploaded file in a new tab.'),

            ]))->title('KYC Document Review')
               ->applyButton('Approve Builder') // The modal's main button also approves
               ->closeButton('Close'),
        ];
    }

    /**
     * Action: Approve Builder
     */
    public function approveBuilder(Request $request)
    {
        // Logic to update database status to 'active'/'verified'
        Toast::success('Builder has been successfully verified and onboarded.');
    }

    /**
     * Action: Reject Builder
     */
    public function rejectBuilder(Request $request)
    {
        // Logic to send rejection email (FR-N-01)
        Toast::warning('Builder application rejected. Notification sent.');
    }
}