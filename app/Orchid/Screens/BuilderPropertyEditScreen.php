<?php

namespace App\Orchid\Screens;

use App\Models\Property;
use App\Models\Project;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Upload;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Color;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Auth;

class BuilderPropertyEditScreen extends Screen
{
    public $property;

    /**
     * Fetch data. Orchid automatically finds the Property from the URL.
     */
    public function query(Property $property): iterable
    {
        // Security: Ensure the builder owns this specific unit/property
        if ($property->exists && $property->partner_id !== Auth::id()) {
            abort(403);
        }

        $property->load('attachment');

        return [
            'property' => $property
        ];
    }

    public function name(): ?string
    {
        return $this->property->exists 
            ? 'Edit Unit: ' . $this->property->title 
            : 'Create New Unit';
    }

    public function commandBar(): iterable
    {
        return [
            Button::make('Save Changes')
                ->icon('check')
                ->method('save')
                ->type(Color::PRIMARY),

            Button::make('Delete Unit')
                ->icon('trash')
                ->method('remove')
                ->confirm('Are you sure you want to delete this unit permanently?')
                ->type(Color::DANGER)
                ->canSee($this->property->exists),
        ];
    }

    public function layout(): iterable
    {
        return [
            Layout::rows([
                
                // 1. Project Selection
                Select::make('property.project_id')
                    ->title('Project')
                    ->fromQuery(Project::where('user_id', Auth::id()), 'name')
                    ->required()
                    ->help('Select the project this unit belongs to.'),

                // 2. Basic Details
                Group::make([
                    Input::make('property.title')
                        ->title('Unit Title')
                        ->placeholder('e.g. Sunrise Apt 401')
                        ->required(),

                    Input::make('property.location')
                        ->title('Location')
                        ->placeholder('e.g. Bandra West, Mumbai')
                        ->required(),
                ]),

                TextArea::make('property.description')
                    ->title('Description')
                    ->rows(3)
                    ->placeholder('Detailed description of the property...'),

                // 3. Type & Configuration
                Group::make([
                    Select::make('property.property_type')
                        ->title('Property Type')
                        ->options([
                            'Apartment' => 'Apartment',
                            'Villa'     => 'Villa',
                            'Plot'      => 'Plot',
                            'Studio'    => 'Studio',
                        ])
                        ->required(),

                    // Changed to simple Input for strings like "3.5" or "Studio"
                    Input::make('property.bhk')
                        ->title('BHK Configuration')
                        ->placeholder('e.g. 2, 3.5, Studio')
                        ->required(),
                ]),

                // 4. Floor Details (New)
                Group::make([
                    Input::make('property.floor_number')
                        ->title('Floor Number')
                        ->type('number')
                        ->placeholder('e.g. 5'),

                    Input::make('property.total_floors')
                        ->title('Total Floors')
                        ->type('number')
                        ->placeholder('e.g. 12'),
                ]),

                // 5. Size & Price
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

                // 6. Status & Timeline (New)
                Group::make([
                    Select::make('property.construction_status')
                        ->title('Construction Status')
                        ->options([
                            'New Launch' => 'New Launch',
                            'Under Construction' => 'Under Construction',
                            'Ready to Move' => 'Ready to Move',
                        ]),

                    DateTimer::make('property.possession_date')
                        ->title('Possession Date')
                        ->format('Y-m-d'),
                ]),

                // 7. Finance & Images
                Select::make('property.financing_option')
                    ->title('Financing Option')
                    ->options([
                        'Loan'         => 'Loan',
                        'Full Payment' => 'Full Payment',
                        'Both'         => 'Both',
                    ])
                    ->empty('Select Option'),

                Upload::make('property.attachment')
                    ->title('Property Images')
                    ->groups('photos')
                    ->maxFiles(5)
                    ->acceptedFiles('image/*'),
            ])
        ];
    }

    public function save(Property $property, Request $request)
    {
        if ($property->exists && $property->partner_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'property.project_id'       => 'required|exists:projects,id',
            'property.title'            => 'required|string|max:255',
            'property.description'      => 'nullable|string',
            'property.location'         => 'required|string|max:255',
            'property.property_type'    => 'required|string',
            'property.bhk'              => 'required|string', // Changed to string
            'property.floor_number'     => 'nullable|integer', // New
            'property.total_floors'     => 'nullable|integer', // New
            'property.carpet_area'      => 'required|numeric',
            'property.price'            => 'required|numeric',
            'property.construction_status' => 'nullable|string', // New
            'property.possession_date'  => 'nullable|date',      // New
            'property.financing_option' => 'nullable|string',
            'property.attachment'       => 'array',
        ])['property'];

        if (!$property->exists) {
            $property->partner_id = Auth::id();
        }

        $property->fill($data)->save();

        $property->attachment()->sync($request->input('property.attachment', []));

        Toast::info('Unit saved successfully.');
        return redirect()->route('platform.builder.properties');
    }

    public function remove(Property $property)
    {
        if ($property->partner_id !== Auth::id()) {
            abort(403);
        }

        $property->attachment()->delete();
        $property->delete();

        Toast::info('Unit deleted successfully.');
        return redirect()->route('platform.builder.properties');
    }
}