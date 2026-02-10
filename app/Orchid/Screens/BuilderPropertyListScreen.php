<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
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
            'properties' => Property::where('partner_id', Auth::id())
                ->with('project')
                ->latest()
                ->paginate(10)
        ];
    }

    public function permission(): ?iterable
    {
        return ['platform.builder.properties'];
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
                TD::make('title', 'Unit Title')->sort()->render(fn(Property $p) => "<strong>{$p->title}</strong>"),
                TD::make('project.name', 'Project')->sort()->render(fn(Property $p) => $p->project->name ?? '-'),
                TD::make('construction_status', 'Status')->sort(), // <--- Added Status to Table
                TD::make('property_type', 'Type')->sort(),
                TD::make('bhk', 'BHK')->sort(), // Now handles strings like "3.5 BHK"
                TD::make('price', 'Price')->sort()->render(fn(Property $p) => '₹ ' . number_format($p->price)),
                TD::make('Actions')
                    ->align(TD::ALIGN_RIGHT)
                    ->render(fn(Property $p) => Link::make('Edit')
                        ->route('platform.builder.properties.edit', $p->id)
                        ->icon('pencil')
                        ->type(Color::LIGHT)),
            ]),

            // 2. MODAL
            Layout::modal('createPropertyModal', Layout::rows([
                
                Select::make('property.project_id')
                    ->title('Select Project')
                    ->fromQuery(Project::where('user_id', Auth::id()), 'name')
                    ->required(),

                Input::make('property.title')
                    ->title('Unit Title')
                    ->placeholder('e.g. Sunrise Apt 401')
                    ->required(),

                Input::make('property.location')
                    ->title('Location')
                    ->placeholder('e.g. Bandra West')
                    ->required(),

                // Row: Type & BHK
                Group::make([
                    Select::make('property.property_type')
                        ->title('Property Type')
                        ->options(['Apartment' => 'Apartment', 'Villa' => 'Villa', 'Plot' => 'Plot', 'Studio' => 'Studio'])
                        ->required(),

                    Input::make('property.bhk')
                        ->title('BHK')
                        ->placeholder('e.g. 2, 3.5, Studio') // Text input now
                        ->required(),
                ]),

                // Row: Floor Details (NEW)
                Group::make([
                    Input::make('property.floor_number')
                        ->title('Floor No.')
                        ->type('number'),

                    Input::make('property.total_floors')
                        ->title('Total Floors')
                        ->type('number'),
                ]),

                // Row: Area & Price
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

                // Row: Status & Timeline (NEW)
                Group::make([
                    Select::make('property.construction_status')
                        ->title('Status')
                        ->options([
                            'New Launch' => 'New Launch',
                            'Under Construction' => 'Under Construction',
                            'Ready to Move' => 'Ready to Move',
                        ]),

                    DateTimer::make('property.possession_date') // Changed from handover_date
                        ->title('Possession Date')
                        ->format('Y-m-d'),
                ]),

                Select::make('property.financing_option')
                    ->title('Financing')
                    ->options(['Loan' => 'Loan', 'Full Payment' => 'Full Payment', 'Both' => 'Both']),

                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),

            ]))->title('Add New Unit')->applyButton('Create'),
        ];
    }

    public function createProperty(Request $request)
    {
        $data = $request->validate([
            'property.project_id'       => 'required|exists:projects,id',
            'property.title'            => 'required|string',
            'property.location'         => 'required|string',
            'property.property_type'    => 'required|string',
            'property.bhk'              => 'required|string', // Changed to string
            'property.floor_number'     => 'nullable|integer', // New
            'property.total_floors'     => 'nullable|integer', // New
            'property.carpet_area'      => 'required|numeric',
            'property.price'            => 'required|numeric',
            'property.construction_status' => 'nullable|string', // New
            'property.possession_date'  => 'nullable|date',      // New (Renamed)
            'property.financing_option' => 'nullable|string',
            'property.attachment'       => 'array',
        ])['property'];

        $data['partner_id'] = Auth::id();
        
        $property = Property::create($data);
        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit created successfully.');
    }
}