<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project; // <--- Ensure this is imported
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Relation; // <--- Ensure this is imported
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            // Filter properties by the logged-in builder (partner_id)
            'properties' => Property::where('partner_id', Auth::id())
                ->with('project') // Eager load project for the table
                ->latest()
                ->paginate(10)
        ];
    }

    public function permission(): ?iterable
    {
        return [
            'platform.builder.properties',
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
                    ->render(fn(Property $property) => "<strong>{$property->title}</strong>"),

                // Project Column
                TD::make('project.name', 'Project')
                    ->sort()
                    ->render(fn(Property $property) => $property->project->name ?? '<span class="text-muted">-</span>'),

                TD::make('location', 'Location')
                    ->sort()
                    ->width('150px'),

                TD::make('property_type', 'Type')->sort(),
                TD::make('bhk', 'BHK')->sort(),

                TD::make('carpet_area', 'Area (sqft)')
                    ->sort()
                    ->render(fn(Property $property) => number_format($property->carpet_area)),

                TD::make('price', 'Price')
                    ->sort()
                    ->render(fn(Property $property) => '₹ ' . number_format($property->price)),

                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(Property $property) => Link::make('Edit')
                        ->route('platform.builder.properties.edit', $property->id)
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. MODAL (Updated)
            Layout::modal('createPropertyModal', Layout::rows([

                // 👇 ADDED: Project Selection Field
                Select::make('property.project_id')
                    ->title('Select Project')
                    // This allows you to write the query directly here 👇
                    ->fromQuery(Project::where('user_id', Auth::id()), 'name')
                    ->required(),

                Input::make('property.title')
                    ->title('Unit Title')
                    ->placeholder('e.g. Sunrise Apt 401')
                    ->required(),

                TextArea::make('property.description')
                    ->title('Description')
                    ->rows(3),

                Input::make('property.location')
                    ->title('Location')
                    ->placeholder('e.g. Bandra West')
                    ->required(),

                Group::make([
                    Select::make('property.property_type')
                        ->title('Property Type')
                        ->options([
                            'Apartment' => 'Apartment',
                            'Villa' => 'Villa',
                            'Plot' => 'Plot',
                            'Studio' => 'Studio',
                        ])
                        ->required(),

                    Input::make('property.bhk')
                        ->title('BHK')
                        ->type('number')
                        ->required(),
                ]),

                Group::make([
                    Input::make('property.carpet_area')
                        ->title('Carpet Area (sqft)')
                        ->type('number')
                        ->required(),

                    Input::make('property.price')
                        ->title('Price (₹)')
                        ->type('number')
                        ->required(),
                ]),

                Group::make([
                    DateTimer::make('property.handover_date')
                        ->title('Handover Date')
                        ->format('Y-m-d'),

                    Select::make('property.financing_option')
                        ->title('Financing')
                        ->options([
                            'Loan' => 'Loan',
                            'Full Payment' => 'Full Payment',
                            'Both' => 'Both',
                        ]),
                ]),

                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Add New Unit')->applyButton('Create'),
        ];
    }

    /**
     * LOGIC: Create New Property
     */
    public function createProperty(Request $request)
    {
        // 3. VALIDATION UPDATE: Include project_id
        $data = $request->validate([
            'property.project_id' => 'required|exists:projects,id', // <--- Added validation
            'property.title' => 'required|string',
            'property.description' => 'nullable|string',
            'property.location' => 'required|string',
            'property.property_type' => 'required|string',
            'property.bhk' => 'required|integer',
            'property.carpet_area' => 'required|numeric',
            'property.price' => 'required|numeric',
            'property.handover_date' => 'nullable|date',
            'property.financing_option' => 'nullable|string',
            'property.attachment' => 'array',
        ])['property'];

        // Auto-assign the logged-in user as the partner
        $data['partner_id'] = Auth::id();

        // Create
        $property = Property::create($data);

        // Attach Images
        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit created successfully.');
    }
}