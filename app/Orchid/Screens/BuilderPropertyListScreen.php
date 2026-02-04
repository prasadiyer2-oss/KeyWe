<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     */
    public function query(): iterable
    {
        // Fetch properties ONLY for projects owned by the logged-in builder
        return [
            'properties' => Property::whereHas('project', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with('project') // Eager load for performance
            ->latest()
            ->paginate(10)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Properties (Units)';
    }

    /**
     * The description is displayed on the user's screen under the heading
     */
    public function description(): ?string
    {
        return 'Manage individual unit inventory, pricing, and availability.';
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Unit')
                ->modal('createPropertyModal')
                ->method('createProperty')
                ->icon('plus')
                ->type(Color::SUCCESS), // KeyWe Green for Primary Action
        ];
    }

    /**
     * Views.
     */
    public function layout(): iterable
    {
        return [
            // 1. Properties Table
            Layout::table('properties', [
                TD::make('title', 'Unit Title')
                    ->sort()
                    ->render(fn (Property $property) => "<strong>{$property->title}</strong>"),

                TD::make('project.name', 'Project')
                    ->render(fn (Property $property) => $property->project->name ?? '-'),

                TD::make('configuration', 'Config')
                    ->sort(),

                TD::make('price', 'Price')
                    ->sort()
                    ->render(fn (Property $property) => '₹ ' . number_format($property->price)),

                TD::make('status', 'Status')
                    ->render(function (Property $property) {
                        $color = match ($property->status) {
                            'Available' => 'text-success',
                            'Reserved' => 'text-warning',
                            'Sold' => 'text-danger',
                            default => 'text-muted',
                        };
                        return "<span class='{$color}'>● {$property->status}</span>";
                    }),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn () => Button::make('Edit')
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. Create Property Modal
            Layout::modal('createPropertyModal', Layout::rows([
                // Relation Field: Only shows projects owned by the current user
                Relation::make('property.project_id')
                ->title('Select Project')
                ->fromModel(Project::class, 'name')
                ->applyScope('byBuilder') // <--- FIXED: Pass the scope name as a string
                ->required()
                ->help('Link this unit to one of your existing projects.'),

                Input::make('property.title')
                    ->title('Unit Title/Number')
                    ->placeholder('e.g. A-101 or Villa 4')
                    ->required(),

                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options([
                        '1BHK' => '1 BHK',
                        '2BHK' => '2 BHK',
                        '3BHK' => '3 BHK',
                        '4BHK+' => '4 BHK+',
                        'Villa' => 'Villa',
                        'Plot' => 'Plot',
                    ])
                    ->required(),

                Input::make('property.area_sqft')
                    ->title('Area (sqft)')
                    ->type('number')
                    ->required(),

                Input::make('property.price')
                    ->title('Price (₹)')
                    ->type('number')
                    ->required(),

            ]))->title('Add New Unit')
               ->applyButton('Save Unit')
               ->closeButton('Cancel'),
        ];
    }

    /**
     * Logic to create a new property
     */
    public function createProperty(Request $request)
    {
        $data = $request->validate([
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
        ])['property'];

        $data['status'] = 'Available';

        Property::create($data);

        Toast::info('Unit created successfully.');
    }
}