<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Relation;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'properties' => Property::whereHas('project', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->with('project')
            ->latest()
            ->paginate(10)
        ];
    }

    public function name(): ?string
    {
        return 'Properties (Units)';
    }

    public function description(): ?string
    {
        return 'Manage individual unit inventory, pricing, and availability.';
    }

    /**
     * FETCH DATA FOR EDITING
     * This runs automatically when you click the "Edit" pencil icon.
     */
    public function asyncGetProperty(Property $property): iterable
    {
        return [
            'property' => $property,
        ];
    }

    public function commandBar(): iterable
    {
        return [
            // ADD UNIT BUTTON (Untouched, uses 'createProperty')
            ModalToggle::make('Add Unit')
                ->modal('createPropertyModal')
                ->method('createProperty')
                ->icon('plus')
                ->type(Color::SUCCESS),
        ];
    }

    public function layout(): iterable
    {
        return [
            // 1. TABLE
            Layout::table('properties', [
                TD::make('title', 'Unit Title')
                    ->sort()
                    ->render(fn (Property $property) => "<strong>{$property->title}</strong>"),

                TD::make('project.name', 'Project')
                    ->render(fn (Property $property) => $property->project->name ?? '-'),

                TD::make('configuration', 'Config')->sort(),

                TD::make('price', 'Price')
                    ->sort()
                    ->render(fn (Property $property) => '₹ ' . number_format($property->price)),

                TD::make('status', 'Status')
                    ->render(fn (Property $property) => match ($property->status) {
                        'Available' => "<span class='text-success'>● {$property->status}</span>",
                        'Reserved' => "<span class='text-warning'>● {$property->status}</span>",
                        'Sold' => "<span class='text-danger'>● {$property->status}</span>",
                        default => "<span class='text-muted'>● {$property->status}</span>",
                    }),

                // ACTIONS COLUMN (Fixed: Using Group::make)
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Property $property) => Group::make([
                        
                        // EDIT BUTTON
                        ModalToggle::make('Edit')
                            ->modal('editPropertyModal') // Uses the NEW separate modal
                            ->method('saveEditedProperty') // Uses the NEW separate method
                            ->async('asyncGetProperty') // Fetches DB data
                            ->asyncParameters(['property' => $property->id])
                            ->icon('pencil')
                            ->type(Color::LIGHT),

                        // DELETE BUTTON
                        Button::make('Delete')
                            ->method('deleteProperty')
                            ->confirm('Are you sure you want to delete this unit?')
                            ->parameters(['property' => $property->id])
                            ->icon('trash')
                            ->type(Color::DANGER),
                    ])),
            ]),

            // 2. CREATE MODAL (Used ONLY by "Add Unit" button)
            Layout::modal('createPropertyModal', Layout::rows([
                Relation::make('property.project_id')
                    ->title('Select Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->applyScope('verified')
                    ->required(),

                Input::make('property.title')
                    ->title('Unit Title')
                    ->placeholder('e.g. A-101')
                    ->required(),

                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options([
                        '1BHK' => '1 BHK', '2BHK' => '2 BHK', '3BHK' => '3 BHK',
                        '4BHK+' => '4 BHK+', 'Villa' => 'Villa', 'Plot' => 'Plot',
                    ])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

            ]))->title('Add New Unit')->applyButton('Create'),

            // 3. EDIT MODAL (New separate modal for Editing)
            Layout::modal('editPropertyModal', Layout::rows([
                // Hidden ID field ensures we update the existing record
                Input::make('property.id')->type('hidden'),

                Relation::make('property.project_id')
                    ->title('Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->applyScope('verified')
                    ->required(),

                Input::make('property.title')->title('Unit Title')->required(),

                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options([
                        '1BHK' => '1 BHK', '2BHK' => '2 BHK', '3BHK' => '3 BHK',
                        '4BHK+' => '4 BHK+', 'Villa' => 'Villa', 'Plot' => 'Plot',
                    ])
                    ->required(),

                // Status field is only available in Edit mode
                Select::make('property.status')
                    ->title('Status')
                    ->options([
                        'Available' => 'Available',
                        'Reserved'  => 'Reserved',
                        'Sold'      => 'Sold',
                    ])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

            ]))->title('Edit Unit Details')->applyButton('Save Changes'),
        ];
    }

    /**
     * LOGIC: Create New Property (Untouched)
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

    /**
     * LOGIC: Save Edited Property (New & Separate)
     */
    public function saveEditedProperty(Request $request)
    {
        $data = $request->validate([
            'property.id' => 'required|exists:properties,id', // Must exist
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.status' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
        ])['property'];

        $property = Property::findOrFail($data['id']);

        // Security check
        if ($property->project->user_id !== Auth::id()) {
            Toast::error('Unauthorized access.');
            return;
        }

        $property->update($data);
        Toast::info('Unit updated successfully.');
    }

    /**
     * LOGIC: Delete Property
     */
    public function deleteProperty(Request $request)
    {
        $property = Property::findOrFail($request->get('property'));

        if ($property->project->user_id !== Auth::id()) {
            Toast::error('Unauthorized access.');
            return;
        }

        $property->delete();
        Toast::info('Unit deleted successfully.');
    }
}