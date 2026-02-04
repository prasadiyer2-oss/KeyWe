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
     * Fetch property data for modal prefill
     */
    public function asyncGetProperty($property): iterable
    {
        $propertyModel = Property::findOrFail($property);
        return ['property' => $propertyModel];
    }

    /**
     * Get project options for dropdown
     */
    public function getProjectOptions(): array
    {
        return Project::where('user_id', Auth::id())
            ->where('verification_status', 'Verified')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Button commands.
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Unit')
                ->modal('createPropertyModal')
                ->method('updateProperty')
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
                    ->render(function (Property $property) {
                        return ModalToggle::make('Edit')
                            ->icon('pencil')
                            ->type(Color::LIGHT)
                            ->modal('createPropertyModal')
                            ->method('updateProperty')
                            ->async('asyncGetProperty')
                            ->asyncParameters(['property' => $property->id])
                            . ' ' .
                            Button::make('Delete')
                                ->icon('trash')
                                ->type(Color::DANGER)
                                ->method('deleteProperty')
                                ->parameters(['property' => $property->id])
                                ->confirm('Are you sure you want to delete this property?');
                    }),
            ]),

            // 2. Create/Edit Property Modal
            Layout::modal('createPropertyModal', Layout::rows([
                Input::make('property.id')
                    ->type('hidden')
                    ->value(null),

                Select::make('property.project_id')
                    ->title('Select Project')
                    ->options($this->getProjectOptions())
                    ->required()
                    ->help('Link this unit to one of your verified projects.'),

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

                Select::make('property.status')
                    ->title('Status')
                    ->options([
                        'Available' => 'Available',
                        'Reserved' => 'Reserved',
                        'Sold' => 'Sold',
                    ]),

            ]))->title('Add New Unit')
               ->applyButton('Save Unit')
               ->closeButton('Cancel'),
        ];
    }

    /**
     * Logic to create or update a property
     */
    public function updateProperty(Request $request)
    {
        $data = $request->validate([
            'property.id' => 'nullable|exists:properties,id',
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
            'property.status' => 'nullable|in:Available,Reserved,Sold',
        ])['property'];

        // If ID is provided, update; otherwise create new
        if (!empty($data['id'])) {
            $property = Property::findOrFail($data['id']);

            // Security check: Ensure builder owns the project
            if ($property->project->user_id !== Auth::id()) {
                Toast::error('Unauthorized access.');
                return;
            }

            unset($data['id']);
            $property->update($data);
            Toast::info('Unit updated successfully.');
        } else {
            // Creating new property
            unset($data['id']);
            $data['status'] = 'Available';
            Property::create($data);
            Toast::info('Unit created successfully.');
        }
    }

    /**
     * Logic to delete a property
     */
    public function deleteProperty(Property $property)
    {
        // Verify that the property belongs to the logged-in builder
        if ($property->project->user_id !== Auth::id()) {
            Toast::error('You do not have permission to delete this property.');
            return;
        }

        $property->delete();

        Toast::info('Unit deleted successfully.');
    }
}