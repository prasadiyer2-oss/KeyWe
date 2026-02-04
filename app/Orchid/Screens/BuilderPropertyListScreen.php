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
use Orchid\Screen\Fields\Upload; // <--- IMPORT THIS
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
     */
    public function asyncGetProperty(Property $property): iterable
    {
        // Load attachments so they appear in the Upload field
        $property->load('attachment'); 

        return [
            'property' => $property,
        ];
    }

    public function commandBar(): iterable
    {
        return [
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

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn (Property $property) => Group::make([
                        ModalToggle::make('Edit')
                            ->modal('editPropertyModal')
                            ->method('saveEditedProperty')
                            ->async('asyncGetProperty')
                            ->asyncParameters(['property' => $property->id])
                            ->icon('pencil')
                            ->type(Color::LIGHT),

                        Button::make('Delete')
                            ->method('deleteProperty')
                            ->confirm('Are you sure you want to delete this unit?')
                            ->parameters(['property' => $property->id])
                            ->icon('trash')
                            ->type(Color::DANGER),
                    ])),
            ]),

            // 2. CREATE MODAL
            Layout::modal('createPropertyModal', Layout::rows([
                Relation::make('property.project_id')
                    ->title('Select Project')
                    ->fromModel(Project::class, 'name')
                    ->applyScope('byBuilder')
                    ->applyScope('verified')
                    ->required(),

                Input::make('property.title')->title('Unit Title')->required(),
                
                Select::make('property.configuration')
                    ->title('Configuration')
                    ->options(['1BHK'=>'1 BHK', '2BHK'=>'2 BHK', '3BHK'=>'3 BHK', '4BHK+'=>'4 BHK+', 'Villa'=>'Villa', 'Plot'=>'Plot'])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

                // --- UPLOAD FIELD ---
                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos') // Logic group name
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Add New Unit')->applyButton('Create'),

            // 3. EDIT MODAL
            Layout::modal('editPropertyModal', Layout::rows([
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
                    ->options(['1BHK'=>'1 BHK', '2BHK'=>'2 BHK', '3BHK'=>'3 BHK', '4BHK+'=>'4 BHK+', 'Villa'=>'Villa', 'Plot'=>'Plot'])
                    ->required(),

                Select::make('property.status')
                    ->title('Status')
                    ->options(['Available'=>'Available', 'Reserved'=>'Reserved', 'Sold'=>'Sold'])
                    ->required(),

                Input::make('property.area_sqft')->title('Area (sqft)')->type('number')->required(),
                Input::make('property.price')->title('Price (₹)')->type('number')->required(),

                // --- UPLOAD FIELD (Auto-fills because of query) ---
                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Edit Unit Details')->applyButton('Save Changes'),
        ];
    }

    /**
     * LOGIC: Create New Property
     */
    public function createProperty(Request $request)
    {
        $data = $request->validate([
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
            // Validate attachments (array of IDs)
            'property.attachment' => 'array', 
        ])['property'];

        $data['status'] = 'Available';
        $property = Property::create($data);

        // SYNC IMAGES
        // Orchid sends an array of Attachment IDs. This syncs them to the property.
        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit created successfully.');
    }

    /**
     * LOGIC: Save Edited Property
     */
    public function saveEditedProperty(Request $request)
    {
        $data = $request->validate([
            'property.id' => 'required|exists:properties,id',
            'property.project_id' => 'required|exists:projects,id',
            'property.title' => 'required|string',
            'property.configuration' => 'required|string',
            'property.status' => 'required|string',
            'property.area_sqft' => 'required|numeric',
            'property.price' => 'required|numeric',
            'property.attachment' => 'array',
        ])['property'];

        $property = Property::findOrFail($data['id']);

        if ($property->project->user_id !== Auth::id()) {
            Toast::error('Unauthorized access.');
            return;
        }

        $property->update($data);

        // SYNC IMAGES (Handles adding new ones and removing deleted ones)
        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit updated successfully.');
    }

    public function deleteProperty(Request $request)
    {
        $property = Property::findOrFail($request->get('property'));

        if ($property->project->user_id !== Auth::id()) {
            Toast::error('Unauthorized access.');
            return;
        }

        // Optional: Delete attachments when property is deleted
        $property->attachment()->delete();
        $property->delete();
        
        Toast::info('Unit deleted successfully.');
    }
}