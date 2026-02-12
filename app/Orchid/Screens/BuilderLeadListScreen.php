<?php

namespace App\Orchid\Screens;

use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;

class BuilderLeadListScreen extends Screen
{
    public function permission(): ?iterable
    {
        return ['platform.builder.leads'];
    }

    public function query(Request $request): iterable
    {
        $user = Auth::user();

        $selectedProjectId = $request->get('project_id');

        $leadsQuery = Lead::query()
            ->with('project')
            ->whereHas('project', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });

        if ($selectedProjectId) {
            $leadsQuery->where('project_id', $selectedProjectId);
        }

        return [
            'leads'       => $leadsQuery->latest()->paginate(10)->withQueryString(),
            'project_id'  => $selectedProjectId,
        ];
    }

    public function name(): ?string
    {
        return 'Lead Inbox';
    }

    public function description(): ?string
    {
        return 'Manage interested buyers and site visit requests.';
    }

    public function commandBar(): iterable
    {
        return [];
    }

    public function layout(): iterable
    {
        return [

            // --- FILTER FORM ---
            Layout::rows([
                Relation::make('project_id')
                    ->title('Filter by Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->empty('All Projects')
                    ->value(request('project_id'))
                    ->chunk(20),

                Button::make('Filter Leads')
                    ->method('filter')
                    ->type(Color::PRIMARY)
                    ->icon('filter'),
            ]),

            // --- LEADS TABLE ---
            Layout::table('leads', [
                TD::make('name', 'Buyer Name')
                    ->sort()
                    ->render(fn(Lead $lead) => "<strong>{$lead->name}</strong>"),

                TD::make('project.title', 'Project Interest')
                    ->render(fn(Lead $lead) => $lead->project->name ?? 'Unknown'),

                TD::make('status', 'Status')
                    ->sort()
                    ->render(function (Lead $lead) {
                        $color = match ($lead->status) {
                            'New'       => 'text-warning',
                            'Contacted' => 'text-info',
                            'Booked'    => 'text-success',
                            'Lost'      => 'text-danger',
                            default     => 'text-muted',
                        };
                        return "<span class='{$color}'>● {$lead->status}</span>";
                    }),

                TD::make('created_at', 'Received')
                    ->sort()
                    ->render(fn(Lead $lead) => $lead->created_at->diffForHumans()),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(Lead $lead) => ModalToggle::make('View Details')
                        ->modal('leadDetailsModal')
                        ->method('markAsContacted')
                        ->asyncParameters(['lead_id' => $lead->id])
                        ->icon('eye')
                        ->type(Color::LIGHT)),
            ]),

            // MODAL
            Layout::modal('leadDetailsModal', Layout::rows([
                Input::make('lead.name')->title('Buyer Name')->readonly(),
                Input::make('lead.phone')->title('Phone Number')->readonly(),
                Input::make('lead.email')->title('Email Address')->readonly(),
                TextArea::make('lead.message')->title('Buyer Message')->rows(3)->readonly(),
            ]))
                ->title('Buyer Lead Pack')
                ->async('asyncGetLead')
                ->applyButton('Mark as Contacted')
                ->closeButton('Close'),
        ];
    }

    /**
     * Filter submit handler
     */
    public function filter(Request $request)
    {
        $projectId = $request->get('project_id');

        return redirect()->route('platform.builder.leads', [
            'project_id' => $projectId,
        ]);
    }

    public function asyncGetLead(Request $request): array
    {
        $lead = Lead::findOrFail($request->get('lead_id'));
        return ['lead' => $lead];
    }

    public function markAsContacted(Request $request)
    {
        $lead = Lead::findOrFail($request->get('lead_id'));
        $lead->status = 'Contacted';
        $lead->save();

        Toast::info('Lead marked as contacted.');
    }
}
