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
    public function query(): iterable
    {
        return [
            // MODIFIED: Fetch ALL users with 'builder' role (removed strict 'pending' check)
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
                
                TD::make('name', 'Organization Name')
                    ->sort()
                    ->render(fn (User $user) => "<strong>{$user->name}</strong>"),

                TD::make('email', 'Email'),

                // MODIFIED: Status Column to show ALL states
                TD::make('verification_status', 'Status')
                    ->sort()
                    ->render(function (User $user) {
                        $status = $user->verification_status;
                        
                        if ($status === 'verified') {
                            return '<span class="text-success">● Verified</span>';
                        } elseif ($status === 'rejected') {
                            return '<span class="text-danger">● Rejected</span>';
                        } elseif ($status === 'pending') {
                            return '<span class="text-warning">● Pending</span>';
                        } else {
                            // Handles NULL or empty
                            return '<span class="text-muted">● Not Set (Null)</span>';
                        }
                    }),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->width('280px')
                    ->render(fn (User $user) => Group::make([
                        
                        ModalToggle::make('Docs')
                            ->modal('reviewDocsModal')
                            ->method('approveBuilder')
                            ->asyncParameters(['user' => $user->id]) 
                            ->icon('eye')
                            ->type(Color::LIGHT),

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

            // Modal logic remains the same
            Layout::modal('reviewDocsModal', Layout::rows([
                Input::make('user.name')->title('Organization')->readonly(),
                Input::make('user.email')->title('Email')->readonly(),
                Label::make('documents_html')->title('Documents')->allowHtml(),
                Input::make('user.id')->type('hidden'),
            ]))
            ->title('Review Documents')
            ->async('asyncGetBuilder')
            ->applyButton('Approve')
            ->closeButton('Close'),
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