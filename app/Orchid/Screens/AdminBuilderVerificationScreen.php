<?php

namespace App\Orchid\Screens;

use App\Models\User;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Fields\Group;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;

class AdminBuilderVerificationScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.admin.builder_verification',
        ];
    }
    
    public function query(): iterable
    {
        return [
            // Fetch ALL users with 'builder' role
            'builders' => User::whereHas('roles', function ($q) {
                    $q->where('slug', 'builder');
                })
                ->with('attachment')
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Builder Verification Queue';
    }

    public function description(): ?string
    {
        return 'Manage all builder accounts and review KYC documents.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [
            Layout::table('builders', [
                TD::make('name', 'Organization')->sort()->render(fn (User $user) => "<strong>{$user->name}</strong>"),
                TD::make('email', 'Email'),
                TD::make('verification_status', 'Status')->render(function (User $user) {
                    return match ($user->verification_status) {
                        'verified' => '<span class="text-success">● Verified</span>',
                        'rejected' => '<span class="text-danger">● Rejected</span>',
                        'pending'  => '<span class="text-warning">● Pending</span>',
                        default    => '<span class="text-muted">● Not Verified</span>',
                    };
                }),
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (User $user) => Group::make([
                        ModalToggle::make('Docs')
                            ->modal('reviewDocsModal')
                            ->method('approveBuilder')
                            ->asyncParameters(['user' => $user->id])
                            ->icon('eye'),
                        Button::make('Approve')
                            ->method('approveBuilder')
                            ->confirm("Verify {$user->name}?")
                            ->parameters(['id' => $user->id])
                            ->icon('check-circle')
                            ->type(Color::SUCCESS),
                        Button::make('Reject')
                            ->method('rejectBuilder')
                            ->confirm('Block this user?')
                            ->parameters(['id' => $user->id])
                            ->icon('x-circle')
                            ->type(Color::DANGER),
                    ])),
            ]),

            // FIX STARTS HERE ------------------------------------------------
            Layout::modal('reviewDocsModal', [
                // Section 1: Read-Only Data (Uses Legend for clean display)
                Layout::legend('user', [
                    \Orchid\Screen\Sight::make('name', 'Organization'),
                    \Orchid\Screen\Sight::make('email', 'Email'),
                    
                    // The Documents List
                    \Orchid\Screen\Sight::make('attachment', 'Documents')
                        ->render(function ($user) {
                            // Loop through documents and generate HTML links
                            if ($user->attachment->isEmpty()) {
                                return '<span class="text-muted">No documents uploaded.</span>';
                            }
                            
                            $html = '';
                            foreach ($user->attachment as $file) {
                                $url = $file->url();
                                $name = $file->original_name;
                                // We use simple HTML here
                                $html .= "<div class='mb-1'><a href='{$url}' target='_blank' class='text-primary'>📄 {$name}</a></div>";
                            }
                            return $html;
                        }),
                ]),
                
                // Section 2: Hidden Input (Required for the Action to know which ID to approve)
                Layout::rows([
                    Input::make('user.id')->type('hidden'),
                ]),
            ])
            ->title('Review Documents')
            ->async('asyncGetBuilder')
            ->applyButton('Approve')
            ->closeButton('Close'),
            // FIX ENDS HERE ------------------------------------------------
        ];
    }

    /**
     * Async Data Loader
     */
    public function asyncGetBuilder(User $user): array
    {
        $docsHtml = '';
        if ($user->attachment->isEmpty()) {
            $docsHtml = '<span class="text-muted">No documents found.</span>';
        } else {
            foreach ($user->attachment as $file) {
                $url = $file->url();
                $name = $file->original_name;
                $docsHtml .= "<div class='mb-2'><a href='{$url}' target='_blank'>⬇️ {$name}</a></div>";
            }
        }

        return [
            'user' => $user,
            'documents_html' => $docsHtml,
        ];
    }

    /**
     * Actions
     */
    public function approveBuilder(Request $request)
    {
        $id = $request->input('id') ?? $request->input('user.id');
        $user = User::findOrFail($id);
        $user->verification_status = 'verified';
        $user->save();
        Toast::success("User verified.");
    }

    public function rejectBuilder(Request $request)
    {
        $user = User::findOrFail($request->input('id'));
        $user->verification_status = 'rejected';
        $user->save();
        Toast::warning("User rejected.");
    }
}